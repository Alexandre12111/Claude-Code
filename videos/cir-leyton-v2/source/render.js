const {chromium}=require('playwright'); const fs=require('fs'); const {spawn}=require('child_process');
const mode=process.argv[2]||'test'; const times=(process.argv[3]||'').split(',').filter(Boolean).map(Number);
(async()=>{
  let html=fs.readFileSync('video.html','utf8').replace('__TIMELINE__',fs.readFileSync('timeline.json','utf8')).replace('__LOGO__',fs.readFileSync('logo_tpl.svg','utf8')).replace('__HAND__',fs.readFileSync('hand.js','utf8'));
  fs.writeFileSync('video_built.html',html);
  const b=await chromium.launch(); const p=await b.newPage({viewport:{width:1920,height:1080}});
  p.on('pageerror',e=>console.log('ERR',e.message)); p.on('console',m=>{if(m.type()==='error')console.log('CONSOLE',m.text())});
  await p.goto('file://'+process.cwd()+'/video_built.html'); await p.waitForFunction('window.READY===true',null,{timeout:30000});
  fs.writeFileSync('sfx.json',JSON.stringify(await p.evaluate(()=>window.SFX)));
  const stage=await p.$('#stage'); const TL=JSON.parse(fs.readFileSync('timeline.json','utf8'));
  if(mode==='test'){ for(const t of times){ await p.evaluate(([t,f])=>window.render(t,f),[t,Math.round(t*30)]); await stage.screenshot({path:`test_${t}.png`}); } }
  else {
    const N=Math.round(TL.total*TL.fps); const pen=[];
    const ff=spawn('ffmpeg',['-y','-loglevel','error','-f','image2pipe','-framerate',String(TL.fps),'-c:v','mjpeg','-i','-','-c:v','libx264','-preset','slow','-crf','16','-pix_fmt','yuv420p','video_noaudio.mp4']);
    for(let f=0;f<N;f++){ const r=await p.evaluate(([t,f])=>window.render(t,f),[f/TL.fps,f]); pen.push(r); const buf=await stage.screenshot({type:'jpeg',quality:94}); if(!ff.stdin.write(buf)) await new Promise(r=>ff.stdin.once('drain',r)); if(f%300===0)console.log('frame',f,'/',N); }
    ff.stdin.end(); await new Promise(r=>ff.on('close',r)); fs.writeFileSync('pen.json',JSON.stringify(pen));
  }
  await b.close();
})();
