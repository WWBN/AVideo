// Run with: node tests/stripe-single-payment-ui.test.js
// Render the actual PHP view using stubs; no application DB or Stripe connection.
const {spawnSync} = require('node:child_process');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
function renderScript(ppv) {
    const rendered = spawnSync('php', ['-r', `class AVideoPlugin {static function getObjectData($n){return (object)['Publishablekey'=>'pk_test_stub','paymentButtonLabel'=>'Pay'];}} function __($s){return $s;} function getToken($t=0){return 'test_token';} $global=['webSiteRootURL'=>'https://test.invalid/','paymentsTest'=>true]; $_GET=['plans_id'=>2]; $_REQUEST=[]; $_SERVER['SCRIPT_NAME']='${ppv ? '/plugin/PayPerView/page/buy.php' : '/plugin/YPTWallet/addFunds.php'}'; include 'plugin/YPTWallet/plugins/YPTWalletStripe/confirmButton.php';`], {encoding:'utf8', cwd:root});
    assert.equal(rendered.status, 0, rendered.stderr);
    return [...rendered.stdout.matchAll(/<script>([\s\S]*?)<\/script>/g)][0][1];
}
const script = renderScript(false), ppvScript = renderScript(true);
const flush = () => new Promise(setImmediate);
function fixture(ppv = false) {
    const state = {ajax:[], confirmations:0, reads:[], disabled:false, messages:[], wallet:0, timers:[], status:'processing', readError:false, credited:false, creditError:false};
    const form = {addEventListener:(name,fn) => state.submit=fn};
    const button = {prop:(k,v) => state.disabled=v, text:v => state.buttonText=v};
    const chain = {ready:fn=>fn(), click:()=>{}, slideToggle:()=>{}, find:()=>button, text:s=>state.messages.push(s), val:()=>1.99};
    const $ = selector => selector === '#videos_id_2' ? {val:()=>3143}
        : selector === '#description2' ? {val:()=>'Wallet funds for PPV rental: Dial M For Murder | 72 hours'} : chain;
    $.ajax = options => {
        state.ajax.push(options);
        if (options.url.endsWith('/singlePaymentStatus.json.php')) {
            assert.equal(options.data.payment_intent_id, 'pi_test');
            assert.equal(options.data.globalToken, 'test_token');
            if (state.creditError) { options.error(); }
            else { options.success({error:false, credited:state.credited}); }
        }
    };
    const context = {
        document:{getElementById:id=>id.startsWith('payment-form')?form:{textContent:''}}, $, webSiteRootURL:'https://test.invalid/',
        Stripe:()=>({elements:()=>({create:()=>({mount:()=>{},addEventListener:()=>{}})}),
            confirmCardPayment:()=>{state.confirmations++;return new Promise((resolve,reject)=>{state.resolve=resolve;state.reject=reject;});},
            retrievePaymentIntent:secret=>{state.reads.push(secret);return state.readError?Promise.reject(new Error('Offline')):Promise.resolve({paymentIntent:{id:'pi_test',status:state.status}});}}),
        modal:{showPleaseWait:()=>{},hidePleaseWait:()=>{}}, avideoToastSuccess:()=>{}, avideoAlertError:()=>{},
        updateYPTWallet:()=>state.wallet++, setTimeout:fn=>state.timers.push(fn)
    };
    vm.runInNewContext(ppv ? ppvScript : script, context);
    state.send = () => state.submit({preventDefault(){}});
    state.confirm = () => {state.send();state.ajax[0].success({error:false,client_secret:'same_test_secret',intent:{id:'pi_test'}});};
    state.gatewayRequests = () => state.ajax.filter(r=>r.url.endsWith('/getIntent.json.php')).length;
    state.tick = async () => {assert.ok(state.timers.length);state.timers.shift()();await flush();};
    return state;
}
(async()=>{
    let s=fixture();s.send();s.send();assert.equal(s.ajax.length,1);assert.equal(s.disabled,true);
    s.ajax[0].success({error:false,client_secret:'same_test_secret',intent:{id:'pi_test'}});s.send();assert.equal(s.ajax.length,1);
    s.resolve({paymentIntent:{id:'pi_test',status:'succeeded'}});await flush();assert.equal(s.wallet,0);assert.equal(s.disabled,true);assert.match(s.messages.at(-1),/Waiting for your account credit/);
    s.credited=true;await s.tick();assert.equal(s.wallet,1);assert.match(s.messages.at(-1),/Funds added successfully/);
    s=fixture();s.send();s.ajax[0].success({already_paid:true,intent:{id:'pi_test'},msg:'Already processed. No new charge.'});assert.equal(s.confirmations,0);assert.equal(s.wallet,0);assert.equal(s.disabled,true);
    s.credited=true;await s.tick();assert.equal(s.wallet,1);
    s=fixture();s.send();s.ajax[0].error();assert.equal(s.disabled,false);assert.match(s.messages.at(-1),/same payment/);
    s=fixture();s.confirm();s.resolve({error:{message:'Card declined'}});await flush();assert.equal(s.disabled,false);assert.equal(s.messages.at(-1),'Card declined');
    s=fixture();s.confirm();s.resolve({paymentIntent:{status:'processing'}});await flush();assert.equal(s.reads.length,1);assert.equal(s.wallet,0);assert.equal(s.disabled,true);
    s.status='succeeded';await s.tick();assert.equal(s.wallet,0);s.credited=true;await s.tick();assert.equal(s.wallet,1);assert.equal(s.gatewayRequests(),1);assert.equal(s.confirmations,1);
    s=fixture();s.confirm();s.resolve({paymentIntent:{status:'processing'}});await flush();
    for(let i=0;i<40;i++){await s.tick();}
    assert.equal(s.disabled,false);assert.equal(s.buttonText,'Check payment status');assert.equal(s.timers.length,0);
    s.status='succeeded';s.credited=true;s.send();s.send();await flush();assert.equal(s.wallet,1);assert.equal(s.gatewayRequests(),1);assert.equal(s.confirmations,1);assert.ok(s.reads.every(secret=>secret==='same_test_secret'));
    s=fixture();s.confirm();s.reject(new Error('Confirmation response lost'));await flush();assert.equal(s.reads.length,1);assert.equal(s.ajax.length,1);
    s.status='requires_payment_method';await s.tick();assert.equal(s.disabled,false);assert.equal(s.buttonText,'Submit Payment');assert.match(s.messages.at(-1),/not completed/);
    s=fixture();s.confirm();s.readError=true;s.resolve({paymentIntent:{status:'processing'}});await flush();for(let i=0;i<40;i++){await s.tick();}
    assert.equal(s.buttonText,'Check payment status');assert.equal(s.ajax.length,1);s.readError=false;s.status='succeeded';s.credited=true;s.send();await flush();assert.equal(s.wallet,1);
    // Approval followed by a failed/delayed webhook must never announce completion or charge again.
    s=fixture();s.confirm();s.creditError=true;s.resolve({paymentIntent:{id:'pi_test',status:'succeeded'}});await flush();
    for(let i=0;i<40;i++){await s.tick();}
    assert.equal(s.wallet,0);assert.equal(s.buttonText,'Check payment status');assert.match(s.messages.at(-1),/account credit is still pending/);
    s.creditError=false;s.credited=true;s.send();assert.equal(s.wallet,1);assert.equal(s.gatewayRequests(),1);assert.equal(s.confirmations,1);
    s=fixture(true);s.send();assert.equal(s.ajax[0].data.plugin,'PayPerView');assert.equal(s.ajax[0].data.videos_id,3143);
    assert.match(s.ajax[0].data.description,/Dial M For Murder/);assert.equal(s.ajax[0].data.plans_id,'2');
    s.credited=true;s.ajax[0].success({already_paid:true,intent:{id:'pi_test'}});
    assert.match(s.messages.at(-1),/Complete your rental on the next page/);assert.equal(s.confirmations,0);
    console.log('PASS: duplicate submit, approval waits for committed credit, already paid, webhook delay/failure, bounded polling, safe retry and network recovery');
})().catch(e=>{console.error(e);process.exitCode=1;});
