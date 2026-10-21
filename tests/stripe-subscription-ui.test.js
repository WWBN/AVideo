// Run with: node tests/stripe-subscription-ui.test.js
const {spawnSync} = require('node:child_process');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const rendered = spawnSync('php', ['-r', `class AVideoPlugin {static function getObjectData($n){return (object)['Publishablekey'=>'pk_test_stub','subscriptionButtonLabel'=>'Subscribe'];}} function __($s){return $s;} function getToken(){return 'test_token';} function getURL($p){return $p;} $global=['webSiteRootURL'=>'https://test.invalid/']; $_GET=['plans_id'=>2,'videos_id'=>640]; include 'plugin/YPTWallet/plugins/YPTWalletStripe/confirmRecurrentButton.php';`], {encoding:'utf8',cwd:root});
assert.equal(rendered.status,0,rendered.stderr);
const source=[...rendered.stdout.matchAll(/<script>([\s\S]*?)<\/script>/g)][0][1];
const flush=()=>new Promise(setImmediate);
function fixture() {
    const s={requests:[],timers:[],tokens:0,confirmations:0,active:false,disabled:false,messages:[],status:'requires_action',wallet:0};
    const form={addEventListener:(event,fn)=>s.submit=fn};
    const ui={prop:(name,value)=>{s.disabled=value;return button;},text:()=>ui,attr:()=>ui,removeClass:()=>ui,addClass:()=>ui,toggleClass:()=>ui,find:()=>button,click:()=>{}};
    const button={...ui,text:value=>{s.buttonText=value;return button;}};
    const $=selector=>typeof selector==='string'&&selector.startsWith('#payment-status')
        ? {...ui,text:value=>{s.messages.push(value);return ui;}} : ui;
    $.ajax=options=>{s.requests.push(options);if(options.url.endsWith('/subscriptionStatus.json.php')){assert.equal(options.data.globalToken,'test_token');options.success({error:false,active:s.active});}};
    const context={$,document:{getElementById:id=>id.startsWith('payment-form')?form:{textContent:''}},webSiteRootURL:'https://test.invalid/',
        window:{top:{location:{href:''}}},modal:{showPleaseWait(){},hidePleaseWait(){}},
        Stripe:()=>({elements:()=>({create:()=>({mount(){},addEventListener(){}})}),createToken:()=>{s.tokens++;return Promise.resolve({token:{id:'tok_test'}});},
            retrievePaymentIntent:()=>Promise.resolve({paymentIntent:{status:s.status}}),
            confirmCardPayment:()=>{s.confirmations++;return new Promise(resolve=>s.resolve=resolve);}}),
        setTimeout:fn=>{s.timers.push(fn);},clearTimeout(){},avideoToastSuccess(){},updateYPTWallet:()=>s.wallet++};
    vm.runInNewContext(source,context);
    s.send=async()=>{s.submit({preventDefault(){}});await flush();};
    s.tick=async()=>{assert.ok(s.timers.length);s.timers.shift()();await flush();};
    s.checkoutRequests=()=>s.requests.filter(r=>r.url.endsWith('/requestSubscription.json.php'));
    s.redirect=()=>context.window.top.location.href;
    return s;
}
(async()=>{
    let s=fixture();await s.send();await s.send();assert.equal(s.tokens,1);assert.equal(s.checkoutRequests().length,1);assert.equal(s.disabled,true);
    assert.equal(s.checkoutRequests()[0].data.globalToken,'test_token');
    s.checkoutRequests()[0].success({error:false});assert.equal(s.wallet,0);assert.match(s.messages.at(-1),/activating/);assert.equal(s.redirect(),'');
    for(let i=0;i<40;i++){await s.tick();}
    assert.equal(s.buttonText,'Check payment status');assert.equal(s.disabled,false);assert.equal(s.wallet,0);
    s.active=true;await s.send();assert.equal(s.wallet,1);assert.equal(s.tokens,1);assert.equal(s.checkoutRequests().length,1);await s.tick();assert.equal(s.redirect(),'https://test.invalid/video/640?autoplay=1');
    s=fixture();await s.send();s.checkoutRequests()[0].success({error:true,confirmCardPayment:true,payment:{latest_invoice:{payment_intent:{client_secret:'test_secret'}}}});await flush();assert.equal(s.confirmations,1);
    await s.send();assert.equal(s.tokens,1);s.resolve({error:{message:'Authentication canceled'}});await flush();assert.equal(s.disabled,false);assert.equal(s.buttonText,'Check payment status');
    s.status='succeeded';s.active=true;await s.send();assert.equal(s.wallet,1);assert.equal(s.tokens,1);assert.equal(s.confirmations,1);
    s=fixture();await s.send();s.checkoutRequests()[0].error();assert.equal(s.disabled,false);assert.match(s.messages.at(-1),/connection was interrupted/);
    console.log('PASS: subscription waits for committed access, duplicate submit, delayed webhook, safe status retry, 3DS cancellation/recovery, exact video redirect and network failure');
})().catch(e=>{console.error(e);process.exitCode=1;});
