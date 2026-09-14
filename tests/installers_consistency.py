"""Cross-project installer assets and completed-page regression checks. No database needed."""
import argparse, os, shutil, subprocess, tempfile
from pathlib import Path
parser = argparse.ArgumentParser()
parser.add_argument('--php', default='php')
parser.add_argument('--encoder', required=True)
parser.add_argument('--network', required=True)
args = parser.parse_args()
streamer = Path(__file__).resolve().parents[1]
projects = [('Streamer', streamer), ('Encoder', Path(args.encoder)), ('Network', Path(args.network))]
for asset in ['installer.css','installer.js','ubuntu-help-functions.php','ubuntu-help.php','assets/favicon.png','assets/logo.png']:
    expected = (streamer/'install'/asset).read_bytes()
    for product, repo in projects: assert (repo/'install'/asset).read_bytes() == expected, (product,asset)
for image in ['favicon.png','logo.png']:
    assert (streamer/'view/img'/image).read_bytes() == (streamer/'install/assets'/image).read_bytes()
print('PASS identical styles, scripts, Ubuntu guidance, canonical logo and favicon')
with tempfile.TemporaryDirectory(prefix='installer-consistency-') as directory:
    for product, repo in projects:
        site = Path(directory)/product
        shutil.copytree(repo/'install',site/'install')
        config = site/('configuration.php' if product == 'Network' else 'videos/configuration.php')
        config.parent.mkdir(parents=True,exist_ok=True)
        config.write_text('<?php throw new Exception("SECRET_SENTINEL");')
        for file in ['index.php','checkConfiguration.php']:
            output = subprocess.check_output([args.php,str(site/'install'/file)],cwd=directory,text=True)
            for secret in ['SECRET_SENTINEL',str(site),'php.ini','Ubuntu setup help','configurationForm','install_csrf_token','name="token"']:
                assert secret not in output,(product,file,secret)
            assert 'Installation' in output
        if product == 'Streamer':
            output = subprocess.check_output([args.php,str(site/'install/cli.php')],cwd=directory,text=True)
            assert not output and not (site/'videos/.initial_admin_password.php').exists()
        print('PASS',product,'locked pages do not bootstrap or expose configuration')

    # Exercise the CLI process boundary without bootstrapping plugins or opening a database.
    site = Path(directory)/'Streamer'; (site/'videos/configuration.php').unlink()
    shutil.copytree(Path(args.encoder)/'install',site/'Encoder/install')
    (site/'install/checkConfiguration.php').write_text("<?php require __DIR__.'/installer.php'; $installerSucceeded = true;")
    (site/'install/installPluginsTables.php').write_text('<?php')
    child = site/'Encoder/install/checkConfiguration.php'
    child.write_text("<?php require __DIR__.'/installer.php'; $installerSucceeded = $_POST['inputPassword'] === getenv('SYSTEM_ADMIN_PASSWORD') && $_POST['databasePort'] === '33317';")
    environment = dict(os.environ, SYSTEM_ADMIN_PASSWORD='cli-qa-secret', DB_MYSQL_HOST='database', DB_MYSQL_PORT='33317', DB_MYSQL_NAME='qa', SERVER_NAME='example.test')
    run = subprocess.run([args.php,str(site/'install/cli.php')],cwd=directory,env=environment,text=True,capture_output=True)
    assert run.returncode == 0,run.stderr
    assert 'cli-qa-secret' not in run.stdout+run.stderr
    child.write_text('<?php $installerSucceeded = false;')
    run = subprocess.run([args.php,str(site/'install/cli.php')],cwd=directory,env=environment,text=True,capture_output=True)
    assert run.returncode == 1,run.stderr
    print('PASS CLI isolates installer helpers, passes credentials through stdin and propagates failure')

    child.write_text('<?php $installerSucceeded = true;')
    environment['SYSTEM_ADMIN_PASSWORD'] = ''
    run = subprocess.run([args.php,str(site/'install/cli.php')],cwd=directory,env=environment,text=True,capture_output=True)
    assert run.returncode == 0,run.stderr
    password_file = site/'videos/.initial_admin_password.php'
    generated = subprocess.check_output([args.php,'-r','echo require $argv[1];',str(password_file)],text=True)
    assert len(generated) == 32 and generated not in run.stdout+run.stderr
    assert subprocess.check_output([args.php,str(password_file)],text=True) == ''
    print('PASS generated CLI password is not logged or rendered by its PHP file')
