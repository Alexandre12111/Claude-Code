const {chromium}=require('playwright');
const B='http://127.0.0.1:8099',S=process.argv[2];
(async()=>{const b=await chromium.launch();const ctx=await b.newContext({viewport:{width:1500,height:1000}});const p=await ctx.newPage();
const errs=[];p.on('pageerror',e=>errs.push('JS: '+e.message));p.on('console',m=>{if(m.type()==='error')errs.push('console: '+m.text())});
p.on('dialog',d=>d.accept());
await p.goto(B+'/wp-login.php');await p.fill('#user_login','admin');await p.fill('#user_pass','admin');await p.click('#wp-submit');await p.waitForLoadState('networkidle');
const menu=await p.locator('#adminmenu a:has-text("Carte des hôtels")').count();console.log('Menu « Carte des hôtels » présent :',menu>0);
await p.goto(B+'/wp-admin/admin.php?page=sapocycle-hotels');await p.waitForLoadState('networkidle');
console.log('Erreurs PHP dans la page :',(await p.content()).match(/Fatal error|Warning:|Notice:|Deprecated:/g)||'aucune');
await p.screenshot({path:'e1-vide.png'});
// Import des 3 pays
const files={CH:'/home/user/Claude-Code/wordpress/sapocycle-hotels/hotels-sapocycle-suisse.xlsx',FR:S+'/harness/test_FR.xlsx',DE:S+'/harness/test_DE.xlsx'};
await p.click('[data-tab=import]');
for(const c of ['CH','FR','DE']){
  await p.click('[data-ic='+c+']');await p.setInputFiles('[data-file]',files[c]);await p.click('[data-import-btn]');
  await p.waitForSelector('[data-result]:not([hidden])',{timeout:60000});
  console.log(c,'→',(await p.textContent('[data-result] .sapo-result__n')).replace(/\s+/g,' ').trim());
  if(c==='CH')await p.screenshot({path:'e2-import.png',fullPage:true});
  await p.waitForFunction(()=>/Terminé/.test(document.querySelector('[data-progress-text]').textContent),{timeout:240000});
  console.log('   localisation :',(await p.textContent('[data-progress-text]')).trim());
  await p.evaluate(()=>{document.querySelector('[data-progress-text]').textContent='';});
}
await p.reload();await p.waitForLoadState('networkidle');
console.log('Statistiques :',await p.$$eval('.sapo-stat',a=>a.map(x=>x.innerText.replace(/\s+/g,' ').trim()).join(' | ')));
await p.screenshot({path:'e3-hotels.png'});
// Édition d'un hôtel
await p.click('[data-rows] tr >> nth=1');await p.waitForTimeout(800);await p.fill('[data-edit] [name=zip]','4056');await p.screenshot({path:'e4-edition.png'});await p.click('[data-save]');await p.waitForTimeout(1200);
console.log('Toast :',await p.textContent('[data-toasts]'));
// Export Suisse
await p.click('[data-tab=import]');await p.click('[data-ic=CH]');const [dl]=await Promise.all([p.waitForEvent('download'),p.click('[data-export]')]);await dl.saveAs(S+'/export-ch.xlsx');console.log('Export :',dl.suggestedFilename());
await p.click('[data-tab=settings]');await p.waitForTimeout(300);await p.screenshot({path:'e5-reglages.png',fullPage:true});
// Pages publiques
for(const [slug,shot] of [['hotels-suisse-fr','f-ch-fr'],['hotels-allemagne-de','f-de-de'],['hotels-france-en','f-fr-en']]){
  const q=await ctx.newPage();q.on('pageerror',e=>errs.push(slug+' JS: '+e.message));
  await q.goto(B+'/?pagename='+slug);await q.waitForLoadState('networkidle');await q.waitForTimeout(1200);
  const n=await q.locator('.sapo-map__item').count();console.log(slug,': hôtels listés =',n,', compteur =',await q.textContent('.sapo-map__count'));
  await q.locator('.sapo-map').scrollIntoViewIfNeeded();await q.screenshot({path:shot+'.png'});
  if(slug==='hotels-suisse-fr'){await q.fill('.sapo-map__q','zermatt');await q.waitForTimeout(800);console.log('  recherche « zermatt » :',await q.textContent('.sapo-map__count'));await q.click('.sapo-map__card >> nth=0');await q.waitForTimeout(1500);await q.screenshot({path:'f-ch-recherche.png'});}
}
console.log('Erreurs JS :',errs.length?errs:'aucune');await b.close();})();
