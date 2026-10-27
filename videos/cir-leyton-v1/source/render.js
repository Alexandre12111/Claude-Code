const {chromium}=require('playwright'); const fs=require('fs'); const {spawn}=require('child_process');
const mode=process.argv[2]||'test'; const times=(process.argv[3]||'').split(',').filter(Boolean).map(Number);
(async()=>{
  let html=fs.readFileSync('video.html','utf8').replace('__TIMELINE__',fs.readFileSync('timeline.json','utf8')).replace('__LOGO__',fs.readFileSync('logo_tpl.svg','utf8'));
  fs.writeFileSync('video_built.html',html);
  const b=await chromium.launch(); const p=await b.newPage({viewport:{width:1920,height:1080}});
  p.on('console',m=>console.log('PAGE',m.text())); p.on('pageerror',e=>console.log('ERR',e.message));
  await p.goto('file://'+process.cwd()+'/video_built.html'); await p.waitForFunction('window.READY===true',null,{timeout:20000});
  const stage=await p.$('#stage');
  if(mode==='test'){ for(const t of times){ await p.evaluate(t=>window.render(t),t); await stage.screenshot({path:`test_${t}.png`}); } }
  else {
    const TL=JSON.parse(fs.readFileSync('timeline.json','utf8')); const N=Math.round(TL.total*TL.fps); const pen=[];
    const ff=spawn('ffmpeg',['-y','-loglevel','error','-f','image2pipe','-framerate',String(TL.fps),'-c:v','mjpeg','-i','-','-c:v','libx264','-preset','slow','-crf','16','-pix_fmt','yuv420p','video_noaudio.mp4']);
    for(let f=0;f<N;f++){ const r=await p.evaluate(t=>window.render(t),f/TL.fps); pen.push(r); const buf=await stage.screenshot({type:'jpeg',quality:95}); if(!ff.stdin.write(buf)) await new Promise(r=>ff.stdin.once('drain',r)); if(f%150===0)console.log('frame',f,'/',N); }
    ff.stdin.end(); await new Promise(r=>ff.on('close',r)); fs.writeFileSync('pen.json',JSON.stringify(pen));
  }
  await b.close();
})();
