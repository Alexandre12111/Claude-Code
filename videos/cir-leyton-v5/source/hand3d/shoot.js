const {chromium}=require('playwright');const fs=require('fs');
(async()=>{const P=JSON.parse(process.argv[2]||'{}');const out=process.argv[3]||'h.png';
const b=await chromium.launch({args:['--use-angle=swiftshader','--enable-unsafe-swiftshader','--ignore-gpu-blocklist']});const p=await b.newPage({viewport:{width:1600,height:1600}});
p.on('pageerror',e=>console.log('ERR',e.message));p.on('console',m=>{if(m.type()==='error'||m.type()==='warning')console.log('C',m.text().slice(0,200))});
await p.addInitScript(P=>{window.P=P},P);
await p.goto('http://localhost:8765/'+(process.env.PAGE||'hand.html')+'');await p.waitForFunction('window.READY',null,{timeout:120000});
const tip=await p.evaluate(()=>window.TIP);console.log('TIP',JSON.stringify(tip));
await (await p.$('#c')).screenshot({path:out,omitBackground:true});await b.close()})();
