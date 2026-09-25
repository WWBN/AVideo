// Run with: node tests/stripe-single-payment-ui.test.js
// Render the actual PHP view using stubs; no application DB or Stripe connection.
const {spawnSync} = require('node:child_process');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const rendered = spawnSync('php', ['-r', `class AVideoPlugin {static function getObjectData($n){return (object)['Publishablekey'=>'pk_test_stub','paymentButtonLabel'=>'Pay'];}} function __($s){return $s;} $global=['webSiteRootURL'=>'https://test.invalid/','paymentsTest'=>true]; $_GET=['plans_id'=>2]; $_REQUEST=[]; include 'plugin/YPTWallet/plugins/YPTWalletStripe/confirmButton.php';`], {encoding:'utf8', cwd:root});
assert.equal(rendered.status, 0, rendered.stderr);
const script = [...rendered.stdout.matchAll(/<script>([\s\S]*?)<\/script>/g)][0][1];
const flush = () => new Promise(setImmediate);
function fixture() {
    const state = {ajax:[], confirmations:0, reads:[], disabled:false, messages:[], wallet:0, timers:[], status:'processing', readError:false};
    const form = {addEventListener:(name,fn) => state.submit=fn};
    const button = {prop:(k,v) => state.disabled=v, text:v => state.buttonText=v};
    const chain = {ready:fn=>fn(), click:()=>{}, slideToggle:()=>{}, find:()=>button, text:s=>state.messages.push(s), val:()=>1.99};
    const $ = () => chain;
    $.ajax = options => state.ajax.push(options);
    const context = {
        document:{getElementById:id=>id.startsWith('payment-form')?form:{textContent:''}}, $, webSiteRootURL:'https://test.invalid/',
        Stripe:()=>({elements:()=>({create:()=>({mount:()=>{},addEventListener:()=>{}})}),
            confirmCardPayment:()=>{state.confirmations++;return new Promise((resolve,reject)=>{state.resolve=resolve;state.reject=reject;});},
            retrievePaymentIntent:secret=>{state.reads.push(secret);return state.readError?Promise.reject(new Error('Offline')):Promise.resolve({paymentIntent:{status:state.status}});}}),
        modal:{showPleaseWait:()=>{},hidePleaseWait:()=>{}}, avideoToastSuccess:()=>{}, avideoAlertError:()=>{},
        updateYPTWallet:()=>state.wallet++, setTimeout:fn=>state.timers.push(fn)
    };
    vm.runInNewContext(script, context);
    state.send = () => state.submit({preventDefault(){}});
    state.confirm = () => {state.send();state.ajax[0].success({error:false,client_secret:'same_test_secret'});};
    state.tick = async () => {assert.ok(state.timers.length);state.timers.shift()();await flush();};
    return state;
}
(async()=>{
    let s=fixture();s.send();s.send();assert.equal(s.ajax.length,1);assert.equal(s.disabled,true);
    s.ajax[0].success({error:false,client_secret:'same_test_secret'});s.send();assert.equal(s.ajax.length,1);
    s.resolve({paymentIntent:{status:'succeeded'}});await flush();assert.equal(s.wallet,1);assert.equal(s.disabled,true);assert.match(s.messages.at(-1),/processed successfully/);
    s=fixture();s.send();s.ajax[0].success({already_paid:true,msg:'Already processed. No new charge.'});assert.equal(s.confirmations,0);assert.equal(s.wallet,1);assert.equal(s.disabled,true);
    s=fixture();s.send();s.ajax[0].error();assert.equal(s.disabled,false);assert.match(s.messages.at(-1),/same payment/);
    s=fixture();s.confirm();s.resolve({error:{message:'Card declined'}});await flush();assert.equal(s.disabled,false);assert.equal(s.messages.at(-1),'Card declined');
    s=fixture();s.confirm();s.resolve({paymentIntent:{status:'processing'}});await flush();assert.equal(s.reads.length,1);assert.equal(s.wallet,0);assert.equal(s.disabled,true);
    s.status='succeeded';await s.tick();assert.equal(s.wallet,1);assert.match(s.messages.at(-1),/processed successfully/);assert.equal(s.ajax.length,1);assert.equal(s.confirmations,1);
    s=fixture();s.confirm();s.resolve({paymentIntent:{status:'processing'}});await flush();
    for(let i=0;i<40;i++){await s.tick();}
    assert.equal(s.disabled,false);assert.equal(s.buttonText,'Check payment status');assert.equal(s.timers.length,0);
    s.status='succeeded';s.send();s.send();await flush();assert.equal(s.wallet,1);assert.equal(s.ajax.length,1);assert.equal(s.confirmations,1);assert.ok(s.reads.every(secret=>secret==='same_test_secret'));
    s=fixture();s.confirm();s.reject(new Error('Confirmation response lost'));await flush();assert.equal(s.reads.length,1);assert.equal(s.ajax.length,1);
    s.status='requires_payment_method';await s.tick();assert.equal(s.disabled,false);assert.equal(s.buttonText,'Submit Payment');assert.match(s.messages.at(-1),/not completed/);
    s=fixture();s.confirm();s.readError=true;s.resolve({paymentIntent:{status:'processing'}});await flush();for(let i=0;i<40;i++){await s.tick();}
    assert.equal(s.buttonText,'Check payment status');assert.equal(s.ajax.length,1);s.readError=false;s.status='succeeded';s.send();await flush();assert.equal(s.wallet,1);
    console.log('PASS: duplicate submit, success, already paid, errors, polling success/failure, bounded polling, safe manual retry and network recovery');
})().catch(e=>{console.error(e);process.exitCode=1;});
