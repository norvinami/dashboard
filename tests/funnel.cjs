'use strict';
const assert=require('node:assert/strict'),fs=require('node:fs'),vm=require('node:vm'),path=require('node:path');
const source=fs.readFileSync(path.join(__dirname,'..','view.php'),'utf8');
const code=source.slice(source.indexOf('function dealStageVisits('),source.indexOf('function renderFunnel('));
const configured=['NEW','6','1','13','2','12','WON','LOSE'];
const live={stageOrder:{0:configured},stageNames:{0:{NEW:'Новая сделка','6':'Потребности','1':'Предложение','13':'Тест-драйв','2':'Подготовка к выдаче','12':'ОТЛОЖИЛ ПОКУПКУ',WON:'Успешно',LOSE:'Провал'}},stageSemantics:{0:{NEW:'P','6':'P','1':'P','13':'P','2':'P','12':'P',WON:'S',LOSE:'F'}},stageHistory:{}};
const context={live};vm.createContext(context);vm.runInContext(code,context);
function deal(id,stage,won=false){return {id:'D'+id,stageId:stage,won,category:0}}
function model(rows){return context.historyFunnel(rows,0,configured)}
const cohort=[];
for(let id=1;id<=100;id++){let stage=id<=30?'13':id<=50?'1':id<=80?'6':'NEW';cohort.push(deal(id,stage));live.stageHistory[id]={NEW:{first:0,last:0},[stage]:{first:1,last:1}}}
let result=model(cohort);assert.deepEqual(Array.from(result.stages,s=>s.count),[100,80,50,30,0]);assert.equal(result.stages[1].converted/result.stages[1].denominator,0.8);assert.equal(result.stages[2].converted/result.stages[2].denominator,0.625);assert.equal(result.stages[3].converted/result.stages[3].denominator,0.6);assert.ok(!result.stages.some(s=>s.id==='12'));
const jump=deal(1001,'13');live.stageHistory[1001]={NEW:{first:0,last:0},'13':{first:1,last:1}};assert.deepEqual(Array.from(model([jump]).stages,s=>s.count),[1,1,1,1,0]);
const returned=deal(1002,'NEW');live.stageHistory[1002]={NEW:{first:0,last:10},'13':{first:3,last:8}};assert.deepEqual(Array.from(model([returned,returned]).stages,s=>s.count),[1,1,1,1,0]);
const hold=deal(1003,'12');live.stageHistory[1003]={NEW:{first:0,last:0},'12':{first:1,last:1}};result=model([hold]);assert.deepEqual(Array.from(result.stages,s=>s.count),[1,0,0,0,0]);assert.equal(result.branches[0].count,1);
const qualifiedHold=deal(1004,'12');live.stageHistory[1004]={'1':{first:0,last:0},'12':{first:1,last:1}};assert.deepEqual(Array.from(model([qualifiedHold]).stages,s=>s.count),[1,1,1,0,0]);
const won=deal(1005,'WON',true);live.stageHistory[1005]={WON:{first:0,last:0}};result=model([won]);assert.deepEqual(Array.from(result.stages,s=>s.count),[1,1,1,1,1]);assert.equal(result.branches[0].count,0);
const lost=deal(1006,'LOSE');live.stageHistory[1006]={'1':{first:0,last:0},LOSE:{first:1,last:1}};assert.deepEqual(Array.from(model([lost]).stages,s=>s.count),[1,1,1,0,0]);
const reopened=deal(1007,'NEW');live.stageHistory[1007]={WON:{first:0,last:0},NEW:{first:1,last:1}};assert.deepEqual(Array.from(model([reopened]).stages,s=>s.count),[1,1,1,1,1]);
assert.ok(context.dealPassedCumulativeTransition(jump,0,'6','1'));assert.ok(context.dealReachedCumulativeStage(jump,0,'1'));assert.ok(!context.dealReachedCumulativeStage(hold,0,'1'));assert.ok(!context.dealPassedCumulativeTransition(jump,0,'NEW','13'));
for(const rows of [[],cohort,[jump,returned,hold,qualifiedHold,won,lost,reopened]]){const stages=model(rows).stages;for(let i=1;i<stages.length;i++){assert.ok(stages[i].count<=stages[i-1].count);assert.ok(stages[i].converted<=stages[i].denominator)}}
assert.equal(context.funnelPercent(62.5),'62,5%');
console.log('PASS: cumulative 100→80→50→30; skipped stages; returns and duplicate visits; unique deals; success/failure; deferred branch; inferred drilldown; monotone bounded conversion.');
