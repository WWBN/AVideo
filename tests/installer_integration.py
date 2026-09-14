"""Installer integration regression tests. Uses only newly created temporary files/databases.
Requires PHP 8.1+ with the installer extensions and a local MySQL account with CREATE/DROP.
Example (XAMPP): python tests/installer_integration.py --mysql D:/xampp/mysql/bin/mysql.exe --php-arg=-d --php-arg=extension=gd --php-arg=-d --php-arg=extension=zip --browser
Optional browser checks require the Python playwright package and installed Chrome.
Database passwords can be supplied with MYSQL_PWD. No application configuration is read.
"""
import argparse,http.cookiejar,json,os,re,shutil,socket,subprocess,tempfile,time,urllib.request,urllib.parse,urllib.error,uuid
from pathlib import Path
parser=argparse.ArgumentParser(description='Test setup using disposable databases and a temporary application directory.')
parser.add_argument('--php',default='php')
parser.add_argument('--mysql',default='mysql')
parser.add_argument('--user',default='root')
parser.add_argument('--port',default=3306,type=int)
parser.add_argument('--php-arg',action='append',default=[])
parser.add_argument('--browser',action='store_true',help='Also test with Playwright and installed Chrome')
args=parser.parse_args()
root=Path(__file__).resolve().parents[1]
workspace=tempfile.TemporaryDirectory(prefix='avideo-installer-test-')
site=Path(workspace.name)
for name in ['install','objects','locale','vendor','videos']:(site/name).mkdir()
for name in ['index.php','checkConfiguration.php','installer.php','installer.js','installer.css','database.sql','ubuntu-help.php','ubuntu-help-functions.php']:
 shutil.copy2(root/'install'/name,site/'install'/name)
shutil.copytree(root/'install/assets',site/'install/assets')
shutil.copy2(root/'objects/bcp47.php',site/'objects/bcp47.php')
for f in (root/'locale').glob('*.php'):(site/'locale'/f.name).write_text('<?php',encoding='utf-8')
for name in ['vendor/autoload.php','vendor/erusev/parsedown/Parsedown.php','node_modules/jquery/dist/jquery.min.js','objects/include_config.php','index.php']:
 (site/name).parent.mkdir(parents=True,exist_ok=True)
 (site/name).write_text('<?php',encoding='utf-8')
php=[args.php]+args.php_arg
mysql=[args.mysql,'--host=127.0.0.1','--port='+str(args.port),'--user='+args.user]
prefix='avideo_qa_'+uuid.uuid4().hex[:10]
dbs=[]
def sql(query):
 return subprocess.check_output(mysql+['--batch','--skip-column-names','--execute='+query],text=True).strip()
def database():
 name=prefix+'_'+str(len(dbs));dbs.append(name);return name
with socket.socket() as s:
 s.bind(('127.0.0.1',0));port=s.getsockname()[1]
log=open(site/'server.log','w')
p=subprocess.Popen(php+['-S',f'127.0.0.1:{port}','-t',str(site)],stdout=log,stderr=log)
url=f'http://127.0.0.1:{port}/install/'
c=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
def post(data):
 req=urllib.request.Request(url+'checkConfiguration.php',urllib.parse.urlencode(data).encode())
 try:r=c.open(req)
 except urllib.error.HTTPError as e:r=e
 return json.loads(r.read())
def payload():
 page=c.open(url).read().decode()
 token=re.search(r'name="install_csrf_token" value="([^"]+)"',page).group(1)
 return dict(install_csrf_token=token,databaseHost='127.0.0.1',databasePort=str(args.port),databaseName=database(),databaseUser=args.user,databasePass=os.environ.get('MYSQL_PWD',''),createTables='2',webSiteRootURL='https://example.test:8443/video/',webSiteTitle="Video's title",mainLanguage='en_US',contactEmail='owner@example.test',systemAdminPass="qa'Password\\special",confirmSystemAdminPass="qa'Password\\special")
try:
 for _ in range(50):
  try:c.open(url);break
  except OSError:time.sleep(.1)
 d=payload()
 assert post({})['error']; print('PASS missing token')
 for key,value in [('databasePort','0'),('contactEmail','bad'),('confirmSystemAdminPass','wrong')]:
  bad=d.copy();bad[key]=value;assert post(bad)['error'];assert not (site/'videos/configuration.php').exists()
 print('PASS input validation without writes')
 test=d.copy();test['action']='test';assert post(test)['error'] is False
 assert sql("SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='"+d['databaseName']+"'")=='0'
 print('PASS connection test does not create database')
 # A connection failure must still produce exactly one JSON response.
 bad=d.copy();bad['databasePort']='1'
 failure=post(bad);assert failure['error'] and failure['stage']=='connection',failure
 assert not (site/'videos/configuration.php').exists()
 print('PASS refused database connection returns JSON')
 # Hold the installer lock from a separate process (the PHP HTTP server is serial).
 locker=subprocess.Popen(php+['-r',"$f=fopen($argv[1], 'c'); flock($f, LOCK_EX); echo 'locked'; fflush(STDOUT); fgets(STDIN);",str(site/'videos/.installer.lock')],stdin=subprocess.PIPE,stdout=subprocess.PIPE)
 try:
  assert locker.stdout.read(6)==b'locked'
  failure=post(d);assert failure['error'] and failure['stage']=='configuration',failure
  assert sql("SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='"+d['databaseName']+"'")=='0'
 finally:
  locker.communicate(b'\n',timeout=5)
 print('PASS installation lock prevents simultaneous writes')
 result=post(d);assert result['error'] is False,result
 assert sql('SELECT COUNT(*) FROM '+d['databaseName']+'.users')=='1'
 assert sql('SELECT COUNT(*) FROM '+d['databaseName']+'.plugins')=='1'
 subprocess.check_call(php+['-l',str(site/'videos/configuration.php')])
 cfg=(site/'videos/configuration.php').read_bytes();assert post(d)['error'];assert cfg==(site/'videos/configuration.php').read_bytes()
 page=c.open(url).read().decode()
 for secret in [str(site), 'install_csrf_token', 'Ubuntu setup help', 'configurationForm', 'php.ini']:
  assert secret not in page,secret
 assert set(post({})).issubset({'error','msg','success'})
 print('PASS complete installation, reinstall lock and private completion page')
 (site/'videos/configuration.php').unlink()
 assert post(d)['error'];assert sql('SELECT COUNT(*) FROM '+d['databaseName']+'.users')=='1'
 print('PASS populated database preserved')
 for mode in ['1','0']:
  d=payload();d['createTables']=mode;sql('CREATE DATABASE '+d['databaseName'])
  if mode=='0':
   subprocess.check_call(mysql+[d['databaseName']],stdin=open(site/'install/database.sql','rb'))
  result=post(d);assert result['error'] is False,result;(site/'videos/configuration.php').unlink()
 print('PASS tables-only and manually imported schema')
 # Seed failure after administrator insertion must roll back that insertion.
 d=payload();d['createTables']='0';sql('CREATE DATABASE '+d['databaseName'])
 subprocess.check_call(mysql+[d['databaseName']],stdin=open(site/'install/database.sql','rb'))
 sql("CREATE TRIGGER "+d['databaseName']+".qa_failure BEFORE INSERT ON "+d['databaseName']+".configurations FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='test failure'")
 result=post(d);assert result['error'],result
 assert sql('SELECT COUNT(*) FROM '+d['databaseName']+'.users')=='0'
 assert not (site/'videos/configuration.php').exists()
 sql('DROP TRIGGER '+d['databaseName']+'.qa_failure')
 result=post(d);assert result['error'] is False,result;(site/'videos/configuration.php').unlink()
 print('PASS failed seed rolls back and retry succeeds')
 # Abort on schema errors, then retry against the empty partially imported schema.
 d=payload();schema=site/'install/database.sql';original=schema.read_text(encoding='utf-8')
 try:
  schema.write_text(original.replace('CREATE TABLE IF NOT EXISTS `categories`', 'BROKEN SQL;\nCREATE TABLE IF NOT EXISTS `categories`',1),encoding='utf-8')
  failure=post(d);assert failure['error'] and failure['stage']=='tables',failure
  assert not (site/'videos/configuration.php').exists()
 finally:
  schema.write_text(original,encoding='utf-8')
 result=post(d);assert result['error'] is False,result;(site/'videos/configuration.php').unlink()
 print('PASS schema import stops on error and retry succeeds')
 # Exercise the actual endpoint under CLI with Docker-style integer options.
 d=payload();d['createTables']=2;d['mainLanguage']='en';d.pop('install_csrf_token')
 runner="$_POST=json_decode(stream_get_contents(STDIN),true); require $argv[1]; exit(!empty($installerSucceeded) ? 0 : 1);"
 cli=subprocess.run(php+['-r',runner,str(site/'install/checkConfiguration.php')],input=json.dumps(d).encode(),capture_output=True,cwd=str(site.parent))
 assert cli.returncode==0,cli.stderr.decode()
 assert json.loads(cli.stdout)['success'] is True
 (site/'videos/configuration.php').unlink()
 print('PASS CLI endpoint, integer setup option and legacy language')
 # The environment CLI must preserve an omitted/empty database password.
 shutil.copy2(root/'install/cli.php',site/'install/cli.php')
 (site/'install/installPluginsTables.php').write_text('<?php',encoding='utf-8')
 d=payload();sql('CREATE DATABASE '+d['databaseName'])
 environment=dict(os.environ,SYSTEM_ADMIN_PASSWORD=d['systemAdminPass'],DB_MYSQL_HOST='127.0.0.1',DB_MYSQL_PORT=str(args.port),DB_MYSQL_NAME=d['databaseName'],DB_MYSQL_USER=args.user,CONTACT_EMAIL=d['contactEmail'],WEBSITE_TITLE=d['webSiteTitle'],MAIN_LANGUAGE=d['mainLanguage'],SERVER_NAME='example.test')
 if os.environ.get('MYSQL_PWD'):environment['DB_MYSQL_PASSWORD']=os.environ['MYSQL_PWD']
 else:environment.pop('DB_MYSQL_PASSWORD',None)
 run=subprocess.run(php+[str(site/'install/cli.php')],cwd=site,env=environment,text=True,capture_output=True)
 assert run.returncode==0,run.stdout+run.stderr
 assert (site/'videos/configuration.php').exists() and d['systemAdminPass'] not in run.stdout+run.stderr
 (site/'videos/configuration.php').unlink()
 print('PASS CLI with optional database password and independent working directory')
 if args.browser:
  from playwright.sync_api import sync_playwright
  with sync_playwright() as pw:
   browser=pw.chromium.launch(channel='chrome',headless=True)
   page=browser.new_page(viewport={'width':1440,'height':1000});errors=[];page.on('pageerror',lambda e:errors.append(str(e)))
   page.goto(url);page.screenshot(path=str(site/'desktop.png'),full_page=True)
   assert page.locator('#mainLanguage').input_value()=='en_US'
   page.locator('#databaseHost').fill('127.0.0.1');page.locator('#databaseName').fill(database())
   page.locator('#testConnection').click();page.wait_for_function("!document.getElementById('configurationForm').hasAttribute('aria-busy')")
   assert 'Connection confirmed' in page.locator('#result').inner_text()
   page.set_viewport_size({'width':390,'height':844});page.screenshot(path=str(site/'mobile.png'),full_page=True)
   assert page.evaluate('document.documentElement.scrollWidth <= window.innerWidth')
   page.locator('#contactEmail').fill('qa@example.test');page.locator('#systemAdminPass').fill('test-password');page.locator('#confirmSystemAdminPass').fill('mismatch')
   page.locator('#installButton').click();assert page.locator('#confirmSystemAdminPass').evaluate('(e)=>!e.validity.valid')
   page.locator('#confirmSystemAdminPass').fill('test-password');page.locator('#installButton').click()
   page.wait_for_function("document.getElementById('result').textContent.includes('Installation complete')")
   assert not errors,errors
   browser.close()
  print('PASS browser desktop/mobile, validation, connection test, installation; no JavaScript errors')
finally:
 p.terminate();p.wait();log.close()
 for db in dbs:
  assert db.startswith(prefix+'_');sql('DROP DATABASE IF EXISTS '+db)

 workspace.cleanup()
