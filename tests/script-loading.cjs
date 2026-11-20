const { chromium } = require('playwright');
const fs = require('fs');
const assert = require('assert/strict');
const path = require('path');
const plugin = fs.readFileSync(path.join(__dirname, '../banner.js'), 'utf8');
(async () => {
 const browser = await chromium.launch({...(process.env.CHROME_PATH ? {executablePath:process.env.CHROME_PATH} : {channel:'chrome'}),headless:true});
 let passed = 0;
 async function test(name, code, consent, expected, url = '') {
  const context = await browser.newContext();
  if (consent !== null) await context.addCookies([{name:'cookie_banner_choices',value:encodeURIComponent(JSON.stringify({analytics:consent})),url:'https://cookie-test.local/'}]);
  const page = await context.newPage();
  const errors = [], requests = [];
  page.on('pageerror', e => errors.push(e.message));
  await page.route('**/*', async route => {
   const u = route.request().url(); requests.push(u);
   if (u === 'https://cookie-test.local/') return route.fulfill({contentType:'text/html',body:'<!doctype html><div id="cookie-banner"><button data-settings></button><section id="cookie-box"><p role="status"></p><input data-category value="analytics"><button data-action="accept"></button></section></div>'});
   return route.fulfill({contentType:'application/javascript',body:'window.order=(window.order||[]);window.order.push("external");'});
  });
  await page.goto('https://cookie-test.local/');
  await page.evaluate(({code,url}) => {window.order=[]; const data=document.createElement('script');data.id='cookie-banner-scripts';data.type='application/json';data.textContent=JSON.stringify({analytics:{code,url}});document.body.append(data);}, {code,url});
  await page.addScriptTag({content:plugin});
  await page.waitForTimeout(150);
  assert.deepEqual(await page.evaluate(() => window.order),expected,name);
  assert.deepEqual(errors,[],name+' errors');
  assert.equal(await page.locator('noscript').count(),0,name+' noscript');
  assert.equal(requests.some(u=>u.includes('mc.yandex.ru/watch/')),false,name+' no noscript pixel');
  assert.equal(await page.locator('head script').evaluateAll(nodes => nodes.some(n=>/^\s*(?:<!--[^]*?-->\s*)?<script\b/i.test(n.textContent))),false,name+' nested script text');
  if (consent !== true) assert.equal(requests.filter(u=>u.includes('counter.js')).length,0,name+' no tracking request');
  await context.close(); console.log('PASS '+name); passed++;
 }
 const snippet='<!-- Yandex.Metrika counter --><script type="text/javascript">window.order.push("metrika");</script><noscript><div><img src="https://mc.yandex.ru/watch/example" alt=""></div></noscript><!-- /Yandex.Metrika counter -->';
 try {
  await test('raw JavaScript', 'window.order.push("raw");',true,['raw']);
  await test('full counter snippet',snippet,true,['metrika']);
  await test('no consent',snippet,null,[]);
  await test('rejected category',snippet,false,[]);
  await test('multiple inline scripts','<script>window.order.push(1)</script><script>window.order.push(2)</script>',true,[1,2]);
  await test('external before inline','<script src="https://cookie-test.local/counter.js"></script><script>window.order.push("inline")</script>',true,['external','inline']);
  await test('URL field before full snippet',snippet,true,['external','metrika'],'https://cookie-test.local/counter.js');
  await test('script tag inside raw JS string','window.order.push("<script>text</script>");',true,['<script>text</script>']);
  await test('external script without consent','<script src="https://cookie-test.local/counter.js"></script>',null,[]);
  await test('external script after rejection','<script src="https://cookie-test.local/counter.js"></script>',false,[]);
  console.log(passed+' browser tests passed');
 } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
