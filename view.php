<?php
if (!defined("BAVARIA_DASHBOARD_ALLOWED")) { require __DIR__."/bootstrap.php"; }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Бавария Павлодар — Панель управления</title>
<?php if (defined('BAVARIA_BITRIX_APP')): ?>
<script src="https://api.bitrix24.com/api/v1/"></script>
<script>
window.BAVARIA_IN_APP=window.parent!==window;
if(window.BAVARIA_IN_APP&&window.BX24){BX24.init(function(){window.BAVARIA_APP_READY=true;BX24.fitWindow()})}
</script>
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#eef2f7; --card:#ffffff; --ink:#0f172a; --ink2:#334155; --muted:#7c8799; --line:#e6ebf2; --row:#f4f7fb;
  --blue:#1668f2; --blue-d:#0b4db5; --blue-l:#b9d4ff; --blue-soft:#e8f0fe; --track:#e7edf5;
  --green:#16a34a; --green-soft:#e7f6ec; --red:#e11d2e; --red-soft:#fdecee; --amber:#d97706; --amber-soft:#fef3e2;
  --radius:14px; --radius-s:9px; --shadow:0 4px 18px rgba(15,35,70,.06),0 1px 2px rgba(15,35,70,.04);
  --font:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif;
}
*{box-sizing:border-box}
html,body{margin:0}
body{background:var(--bg);color:var(--ink);font:14px/1.4 var(--font);-webkit-font-smoothing:antialiased;overflow-x:hidden}
button,input,select,textarea{font:inherit;color:inherit}
button{cursor:pointer}
.wrap{max-width:1480px;margin:0 auto;padding:0 20px 32px}
svg.i{width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex:none}

/* header */
.top{display:flex;align-items:center;gap:18px;padding:16px 0;flex-wrap:wrap}
.brand{display:flex;align-items:center;gap:12px;min-width:0;cursor:pointer}
.mono{width:44px;height:44px;border-radius:50%;background:radial-gradient(circle at 35% 30%,#2b7bff,#0b3d91);color:#fff;display:grid;place-items:center;font-weight:800;font-size:15px;letter-spacing:.5px;box-shadow:inset 0 0 0 3px #fff,0 0 0 2px #0f172a}
.brand b{display:block;font-size:17px;line-height:1.05;font-weight:800;letter-spacing:.2px}
.brand small{display:block;font-size:10px;color:var(--muted);margin-top:2px}
.tag{font-size:11px;letter-spacing:1px;color:var(--ink2);text-transform:uppercase;line-height:1.5;margin-left:18px}
.spacer{flex:1}
.pill-btn{display:flex;align-items:center;gap:10px;background:var(--card);border:1px solid var(--line);border-radius:12px;padding:9px 14px;box-shadow:var(--shadow);white-space:nowrap}
.pill-btn:hover{border-color:#c9d6ea}
.pill-btn .i{color:var(--blue)}
.btn{display:inline-flex;align-items:center;gap:8px;border:0;border-radius:10px;padding:9px 14px;font-weight:600;white-space:nowrap}
.btn-p{background:var(--blue);color:#fff}.btn-p:hover{background:#0f5ad8}
.btn-g{background:var(--blue-soft);color:var(--blue)}.btn-g:hover{background:#dbe7fd}
.btn-o{background:#fff;border:1px solid var(--line);color:var(--ink2)}.btn-o:hover{border-color:#c9d6ea}
.btn-s{padding:6px 10px;font-size:12.5px;border-radius:8px}
.user{display:flex;align-items:center;gap:10px}
.ava{width:40px;height:40px;border-radius:50%;display:grid;place-items:center;color:#fff;font-weight:700;font-size:13px;flex:none}
.ava.sm{width:28px;height:28px;font-size:10.5px}
.ava.lg{width:64px;height:64px;font-size:20px}
.user b{display:block;font-size:13.5px}.user small{font-size:11.5px;color:var(--muted)}
.dd{position:relative}
.menu{position:absolute;right:0;top:calc(100% + 6px);background:#fff;border:1px solid var(--line);border-radius:12px;box-shadow:0 12px 32px rgba(15,35,70,.14);padding:6px;min-width:230px;z-index:40;display:none}
.menu.open{display:block}
.menu button{display:flex;width:100%;text-align:left;align-items:center;gap:10px;border:0;background:none;padding:9px 10px;border-radius:8px}
.menu button:hover,.menu button.on{background:var(--blue-soft);color:var(--blue)}

/* hero */
.hero{position:relative;display:flex;align-items:flex-end;min-height:118px;margin-bottom:14px}
.hero h1{margin:0;font-size:30px;font-weight:800;letter-spacing:-.4px}
.hero nav{display:flex;flex-wrap:wrap;gap:4px 0;margin-top:4px;font-size:16.5px;color:var(--ink2)}
.hero nav a{color:inherit;text-decoration:none;cursor:pointer}
.hero nav a:hover{color:var(--blue)}
.hero nav span{margin:0 7px;color:var(--muted)}
.hero-txt{position:relative;z-index:1;padding-bottom:4px}
.hero-img{position:absolute;right:-20px;top:-18px;bottom:-4px;width:min(56%,760px);pointer-events:none;
  -webkit-mask-image:linear-gradient(90deg,transparent 0,#000 38%);mask-image:linear-gradient(90deg,transparent 0,#000 38%)}
.hero-img svg{width:100%;height:100%;display:block}

/* grid */
.kpis{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px;margin-bottom:14px}
.card{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);min-width:0}
.kpi{padding:16px 16px 14px;display:flex;gap:12px;border:1px solid transparent;text-align:left;transition:.15s}
button.kpi:hover{border-color:#c9d6ea;transform:translateY(-1px)}
.kpi .ic{color:var(--blue);padding-top:2px}
.kpi .ic svg{width:34px;height:34px;stroke-width:1.8}
.kpi.warn .ic{color:var(--red)}
.kpi .lb{font-size:12.5px;font-weight:600;line-height:1.2;min-height:30px;overflow-wrap:break-word}
.kpi .val{font-size:27px;font-weight:800;letter-spacing:-.5px;line-height:1.1;margin:2px 0 6px;white-space:nowrap}
.kpi .val small{font-size:16px;font-weight:700;margin-left:3px}
.delta{font-size:12.5px;font-weight:700}
.delta.up{color:var(--green)}.delta.bad{color:var(--red)}.delta.dn{color:var(--red)}.delta.na{color:var(--muted);font-weight:500}
.kpi .cmp{font-size:10.5px;color:var(--muted)}

.mid{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(0,.95fr) minmax(0,1.1fr);gap:12px;margin-bottom:14px}
.panel{padding:16px 18px}
.ph{display:flex;align-items:flex-start;gap:10px;margin-bottom:12px}
.ph .i{width:30px;height:30px;color:var(--blue);stroke-width:1.9}
.ph h2{margin:0;font-size:17px;font-weight:800;line-height:1.3}
.ph p{margin:1px 0 0;font-size:12px;color:var(--ink2)}
.chip{margin-left:auto;display:flex;align-items:center;gap:6px;background:#fff;border:1px solid var(--line);border-radius:8px;padding:5px 9px;font-size:12px;font-weight:500;white-space:nowrap}
.chip .i{width:14px;height:14px;color:var(--ink2)}
.chip:hover{border-color:#c9d6ea}

.fun-wrap{display:flex;align-items:center;gap:16px}
.funnel{flex:1;min-width:0}
.frow{display:grid;grid-template-columns:minmax(0,98px) minmax(0,1fr) 40px 40px;align-items:center;gap:8px;height:27px;cursor:pointer;border-radius:6px;padding:0 4px}
.frow:hover{background:var(--row)}
.frow .fn{font-size:12.3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.frow .fb{height:25px;display:flex;justify-content:center}
.frow .fb i{display:block;height:100%}
.frow .fc{font-weight:700;font-size:12.5px;text-align:right}
.frow .fp{font-size:12px;color:var(--ink2);text-align:right}
.donut{position:relative;flex:none;display:grid;place-items:center}
.donut .dl{position:absolute;text-align:center;line-height:1.1}
.donut .dl b{display:block;font-size:24px;font-weight:800;letter-spacing:-.5px}
.donut .dl span{font-size:11px;color:var(--ink2)}
.donut circle.arc{transition:stroke-dasharray .5s}

.svc{display:flex;align-items:center;gap:22px}
.svc-stats{flex:1;min-width:0;display:flex;flex-direction:column;gap:6px}
.stat{display:flex;justify-content:space-between;gap:10px;font-size:13px;padding:8px 10px;border-radius:8px;cursor:pointer}
.stat:hover{background:var(--row)}
.stat b{font-weight:700}
.svc-bar{height:6px;border-radius:4px;background:var(--track);overflow:hidden;margin:2px 10px 0}
.svc-bar i{display:block;height:100%;background:var(--blue)}

table{width:100%;border-collapse:collapse}
.tw{overflow-x:auto;-webkit-overflow-scrolling:touch}
th{font-size:12px;font-weight:600;color:var(--ink2);text-align:left;background:var(--row);padding:8px 10px;white-space:nowrap}
th:first-child{border-radius:8px 0 0 8px}th:last-child{border-radius:0 8px 8px 0}
td{padding:8px 10px;border-bottom:1px solid var(--line);font-size:13px;vertical-align:middle}
tr:last-child td{border-bottom:0}
tbody tr.cl{cursor:pointer}
tbody tr.cl:hover td{background:#f8fafd}
.num{text-align:center}
.r{text-align:right}
.src{display:flex;align-items:center;gap:9px;white-space:nowrap}
.sico{width:22px;height:22px;border-radius:6px;display:grid;place-items:center;flex:none}
.sico svg{width:14px;height:14px;stroke:#fff;stroke-width:2.2;fill:none}
.person{display:flex;align-items:center;gap:9px;white-space:nowrap}
.red{color:var(--red);font-weight:700}
.badge{display:inline-block;padding:3px 8px;border-radius:20px;font-size:11.5px;font-weight:600;white-space:nowrap}
.b-blue{background:var(--blue-soft);color:var(--blue)}.b-green{background:var(--green-soft);color:var(--green)}
.b-red{background:var(--red-soft);color:var(--red)}.b-amber{background:var(--amber-soft);color:var(--amber)}.b-gray{background:#eef1f5;color:#5b6474}
.tasks-card{padding:16px 18px}
.btn svg.i{width:16px;height:16px}
.tcomp th,.tcomp td{padding:8px 6px;font-size:12.5px}
.tcomp .src{gap:7px}
.tasks-card .ph .i{color:var(--red)}

/* list pages */
.crumbs{display:flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);margin:4px 0 10px;flex-wrap:wrap}
.crumbs a{color:var(--blue);cursor:pointer;text-decoration:none}
.page-h{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px}
.page-h h1{margin:0;font-size:26px;font-weight:800;letter-spacing:-.3px}
.page-h .cnt{color:var(--muted);font-weight:600;font-size:18px}
.filters{display:flex;gap:8px;flex-wrap:wrap;padding:12px;border-bottom:1px solid var(--line)}
.inp{border:1px solid var(--line);border-radius:9px;padding:8px 11px;background:#fff;min-width:0}
.inp:focus{outline:2px solid #bcd4fb;border-color:var(--blue)}
.search{flex:1 1 220px;display:flex;align-items:center;gap:8px}
.search input{border:0;outline:0;flex:1;min-width:0;background:none;padding:0}
.search .i{width:16px;height:16px;color:var(--muted)}
.sum-row{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:14px}
.sum{padding:12px 16px;flex:1 1 150px}
.sum small{font-size:12px;color:var(--muted)}.sum b{display:block;font-size:22px;font-weight:800}
.empty{padding:40px;text-align:center;color:var(--muted)}
.more{padding:10px;text-align:center}
.emp-head{display:flex;gap:16px;align-items:center;padding:18px;flex-wrap:wrap;margin-bottom:14px}
.emp-head h1{margin:0;font-size:22px}
.emp-head p{margin:2px 0 0;color:var(--ink2)}
.stages{display:flex;gap:4px;margin:12px 0}
.stages i{flex:1;height:6px;border-radius:3px;background:var(--track)}
.stages i.on{background:var(--blue)}
.ck{width:20px;height:20px;border-radius:6px;border:2px solid #c3cddb;background:#fff;display:grid;place-items:center;padding:0}
.ck.on{background:var(--green);border-color:var(--green)}
.ck.on::after{content:'';width:9px;height:5px;border:2px solid #fff;border-top:0;border-right:0;transform:rotate(-45deg) translate(1px,-1px)}
.done-t{color:var(--muted);text-decoration:line-through}

/* modal */
.ov{position:fixed;inset:0;background:rgba(15,23,42,.42);display:none;align-items:center;justify-content:center;z-index:90;padding:16px}
.ov.open{display:flex}
.modal{background:#fff;border-radius:16px;width:min(520px,100%);max-height:calc(100vh - 32px);overflow:auto;box-shadow:0 24px 60px rgba(0,0,0,.25)}
.mh{display:flex;align-items:center;padding:16px 18px;border-bottom:1px solid var(--line)}
.mh h3{margin:0;font-size:17px;flex:1}
.x{border:0;background:none;padding:4px;border-radius:6px;color:var(--muted)}.x:hover{background:var(--row)}
.mb{padding:16px 18px;display:grid;gap:12px}
.mb label{display:grid;gap:5px;font-size:12.5px;font-weight:600;color:var(--ink2)}
.mb .two{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.mf{display:flex;justify-content:flex-end;gap:8px;padding:12px 18px 16px;flex-wrap:wrap}
.kv{display:grid;grid-template-columns:130px minmax(0,1fr);gap:6px 12px;font-size:13px}
.kv span{color:var(--muted)}
.err{color:var(--red);font-size:12px;display:none}
.toast{position:fixed;left:50%;bottom:22px;transform:translateX(-50%) translateY(20px);background:#0f172a;color:#fff;padding:11px 16px;border-radius:10px;font-size:13px;opacity:0;transition:.25s;z-index:100;pointer-events:none;max-width:calc(100% - 32px)}
.toast.show{opacity:1;transform:translateX(-50%)}

/* responsive */
@media (max-width:1320px){.kpis{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media (max-width:1240px){.mid{grid-template-columns:minmax(0,1fr) minmax(0,1fr)}.mid .p-fun{grid-column:1/-1}}
@media (max-width:980px){.tag{display:none}.hero-img{width:70%;opacity:.55}}
@media (max-width:820px){.mid{grid-template-columns:minmax(0,1fr)}.kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (max-width:640px){
  .wrap{padding:0 16px 24px}
  .top{gap:10px}
  .top .spacer{display:none}
  .user .ut{display:none}
  .datebtn{order:5;flex:1 1 100%;justify-content:space-between}
  .hero{min-height:0}.hero-img{display:none}
  .hero h1{font-size:24px}.hero nav{font-size:14px}
  .fun-wrap,.svc{flex-direction:column;align-items:stretch}
  .donut{align-self:center}
  .kpi{padding:12px;gap:8px;flex-direction:column}
  .kpi .ic svg{width:28px;height:28px}
  .kpi .val{font-size:23px}
  .kpi .lb{min-height:0}
  .mb .two{grid-template-columns:1fr}
  .kv{grid-template-columns:110px minmax(0,1fr)}
  .page-h h1{font-size:21px}
  .create-lbl{display:none}
}

.funnel-picker{display:flex;margin-bottom:12px}
.funnel-picker .inp{width:100%;min-width:0;font-size:12px}
.funnel-outcomes{display:flex;gap:8px;margin-top:12px}
.funnel-outcome{flex:1;display:flex;justify-content:space-between;align-items:center;border:0;border-radius:10px;padding:10px 12px;font:inherit;font-size:12px;cursor:pointer}
.funnel-outcome.success{background:#edf8f0;color:#25864d}
.funnel-outcome.failed{background:#fff0ef;color:#b84d47}
.funnel-outcome b{font-size:17px}
.funnel-more{margin-top:8px}
.funnel-link{display:block;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;border:0;background:none;padding:0;font:inherit;text-align:left;color:var(--blue);cursor:pointer}
.funnel-overview th,.funnel-overview td{padding:9px 6px;font-size:11px}

.date-range{display:flex;align-items:flex-end;gap:8px;padding:8px 10px;background:#fff;border:1px solid var(--line);border-radius:12px;box-shadow:var(--shadow)}
.date-range label{display:grid;gap:2px;font-size:11px;color:var(--muted)}
.date-range input{min-width:0;width:130px;border:1px solid var(--line);border-radius:7px;padding:5px;background:#fff;font-size:12px}
.date-chip{font-size:10px;white-space:nowrap;cursor:default}
.lead-panel{margin-bottom:14px}
.lead-filters{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:18px}
.lead-filters label{display:grid;gap:5px;font-size:12px;color:var(--ink2);min-width:190px}
.lead-filters .inp{font-size:12px}
.lead-chart-row{margin-bottom:20px}
.lead-chart-title{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:8px}
.lead-chart-title span{font-size:12px;color:var(--ink2)}
.lead-stacked{display:flex;height:32px;border-radius:8px;overflow:hidden;background:var(--track)}
.lead-segment{display:flex;align-items:center;justify-content:center;color:white;min-width:0;overflow:hidden;font-size:12px;font-weight:700}
.lead-segment.successful,.lead-dot.successful,.lead-segment.qualified,.lead-dot.qualified{background:#16a34a}
.lead-segment.failed,.lead-dot.failed{background:#e16a68}
.lead-segment.open,.lead-dot.open{background:#1668f2}
.lead-segment.unconfirmed,.lead-dot.unconfirmed{background:#d9972c}
.lead-row-counts{display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;font-size:12px;color:var(--ink2)}
.lead-dot{display:inline-block;width:8px;height:8px;margin-right:4px;border-radius:3px}
.lead-totals{display:flex;gap:22px;flex-wrap:wrap;border-top:1px solid var(--line);padding-top:12px;font-size:13px}
.lead-totals b{margin-left:6px}
.lead-note{font-size:11px;color:var(--muted);margin:10px 0 0}
@media(max-width:640px){.date-range{width:100%;flex-wrap:wrap}.date-range label{flex:1}.date-range input{width:100%}.lead-chart-title{align-items:flex-start;flex-direction:column}.lead-filters label{width:100%}.date-chip{white-space:normal;text-align:right;max-width:130px}.lead-totals{gap:10px}}

.p-fun .frow,.funnel-columns{grid-template-columns:minmax(0,98px) minmax(0,1fr) 35px 52px}
.funnel-columns{display:grid;align-items:center;gap:8px;padding:0 4px;margin-bottom:5px;font-size:10px;color:var(--muted)}
.funnel-columns span:nth-child(n+3){text-align:right}
.funnel-history-note{font-size:11px;color:var(--muted);line-height:1.5;margin:10px 0 0}

.p-fun .transition-row,.p-fun .funnel-columns{grid-template-columns:minmax(0,138px) minmax(0,1fr) 64px 52px}
.p-fun .transition-row{height:auto;min-height:42px;padding-top:5px;padding-bottom:5px}
.p-fun .transition-row .fn{white-space:normal;line-height:1.25;font-size:11px}
.p-fun .transition-row .fb{height:9px;background:var(--track);border-radius:4px;overflow:hidden;justify-content:flex-start}
.p-fun .transition-row .fc{font-size:11px;font-variant-numeric:tabular-nums}
.p-fun .transition-row .fp{font-size:12px;font-weight:700;color:var(--blue)}

.activity-report{margin-bottom:18px}
.activity-controls{display:flex;align-items:flex-end;gap:12px;margin-bottom:14px;flex-wrap:wrap}
.activity-controls label{display:grid;gap:4px;min-width:220px;flex:1;font-size:12px;color:var(--ink2)}
.activity-controls span{font-size:11px;color:var(--muted);padding-bottom:8px}
.activity-report table{min-width:920px}
.activity-report th{font-size:10px;text-transform:uppercase;white-space:nowrap}
.activity-report td{font-size:12px}
.activity-report .activity-zero{color:#c8d0db}
.activity-report .activity-risk{background:#fdeeee;color:#ce4c50;font-weight:700}
.activity-report .activity-warn{background:#fff5e5;color:#b87929;font-weight:700}
.activity-report tfoot td{font-weight:700;background:#f5f8fc;border-top:1px solid var(--line)}
.activity-note{font-size:11px;color:var(--muted);margin:10px 0 0}
.funnel-branches{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}.funnel-branch{display:flex;justify-content:space-between;gap:12px;align-items:center;width:100%;padding:9px 12px;background:#fff5e5;color:#9a671e;border:0;border-radius:9px;font:inherit;font-size:12px}
</style>
</head>
<body>
<div class="wrap">
  <header class="top">
    <div class="brand" data-go="#/">
      <div class="mono">БП</div>
      <div><b>БАВАРИЯ<br>ПАВЛОДАР</b><small>Премиальный автоцентр</small></div>
    </div>
    <div class="tag">Премиальные автомобили.<br>Сервис, которому доверяют.</div>
    <div class="spacer"></div>
    <div class="dd"><button class="pill-btn" id="pipelineBtn" title="Фильтр воронок Bitrix"><svg class="i"><use href="#i-hand"/></svg><span id="pipelineLbl">Воронки</span><svg class="i"><use href="#i-chev"/></svg></button></div>
    <form class="date-range" id="dateRangeForm" method="get">
      <label>С<input type="date" name="from" id="dateFrom" required aria-label="Дата с"></label>
      <label>По<input type="date" name="to" id="dateTo" required aria-label="Дата по"></label>
      <button class="btn btn-p btn-s" type="submit">Применить</button>
    </form>
    <div class="pill-btn user">
      <div class="ava" style="background:linear-gradient(135deg,#8a6a58,#4b3a33)">ОШ</div>
      <div class="ut"><b>Оксана Шлегель</b><small>Только чтение</small></div>
      <svg class="i ut" style="width:16px;color:var(--ink2)"><use href="#i-chev"/></svg>
    </div>
  </header>
  <main id="app"></main><p style="color:var(--muted);font-size:12px;margin:16px 0">Период — дата создания лида для графика обращений и дата создания сделки для остальных показателей. Причина обращения берётся из текущего значения поля лида. Показаны только доступные Оксане данные CRM и задач. Суммы успешных сделок в ₸, не поступления оплаты. Воронка накопительная: предыдущие основные этапы засчитываются по максимальному достигнутому; отложенный этап учитывается отдельно. Данные 1С и поля автомобиля пока не подключены. Обновление — при перезагрузке страницы.</p>
</div>

<div class="ov" id="ov"><div class="modal" id="modal"></div></div>
<div class="toast" id="toast"></div>

<svg width="0" height="0" style="position:absolute">
  <defs>
    <symbol id="i-coins" viewBox="0 0 24 24"><ellipse cx="10" cy="5" rx="7" ry="2.6"/><path d="M3 5v4c0 1.4 3.1 2.6 7 2.6s7-1.2 7-2.6V5M3 9v4c0 1.4 3.1 2.6 7 2.6M3 13v4c0 1.4 3.1 2.6 7 2.6"/><ellipse cx="16" cy="14" rx="5.5" ry="2.2"/><path d="M10.5 14v4.5c0 1.2 2.5 2.2 5.5 2.2s5.5-1 5.5-2.2V14"/></symbol>
    <symbol id="i-car" viewBox="0 0 24 24"><path d="M3 13l2.1-5.2A2 2 0 0 1 7 6.5h10a2 2 0 0 1 1.9 1.3L21 13v4a1 1 0 0 1-1 1h-1.5M3 13v4a1 1 0 0 0 1 1h1.5M3 13h18M9.3 18h5.4"/><circle cx="7.4" cy="17.5" r="1.9"/><circle cx="16.6" cy="17.5" r="1.9"/></symbol>
    <symbol id="i-wrench" viewBox="0 0 24 24"><path d="M14.7 6.3a4 4 0 0 0 5.3 5.3l-9.4 9.4a2.1 2.1 0 0 1-3-3z"/><path d="M4 4l3 1 5 5-2 2-5-5z"/><path d="M13 14l6 6"/></symbol>
    <symbol id="i-users" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0z"/><circle cx="17" cy="9" r="2.8"/><path d="M16.5 14.2A5.5 5.5 0 0 1 21.5 20h-3.5"/></symbol>
    <symbol id="i-hand" viewBox="0 0 24 24"><path d="M2 11l4-4 3 1 3-2 3 1 3-1 4 4-3 4"/><path d="M9 8l-2 3a1.5 1.5 0 0 0 2.4 1.7L12 11l5 5-1.5 1.5M14 18.5l-1.5 1.5M10.5 18l-1.5 1.5M6 14l2.5 2.5"/></symbol>
    <symbol id="i-bell" viewBox="0 0 24 24"><path d="M6 9a6 6 0 0 1 12 0c0 5 2 7 2 7H4s2-2 2-7M10 20a2 2 0 0 0 4 0"/><path d="M3 5l2-2M21 5l-2-2"/></symbol>
    <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2.5 2M4 4l3-2M20 4l-3-2"/></symbol>
    <symbol id="i-bars" viewBox="0 0 24 24"><path d="M4 20V13M9 20V9M14 20v-8M19 20V4M2 21h20"/></symbol>
    <symbol id="i-cal" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4M7 14h2M11 14h2M15 14h2M7 17h2M11 17h2"/></symbol>
    <symbol id="i-chev" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></symbol>
    <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
    <symbol id="i-x" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></symbol>
    <symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></symbol>
    <symbol id="i-cam" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="5"/><circle cx="12" cy="12" r="3.6"/><circle cx="16.8" cy="7.2" r=".6"/></symbol>
    <symbol id="i-chat" viewBox="0 0 24 24"><path d="M4 20l1.3-3.8A8 8 0 1 1 8 19z"/><path d="M9 9.5c.3 2 2.3 4.3 5 5l1-1.2-1.6-.8-.8.8c-1-.4-2-1.4-2.4-2.4l.8-.8-.8-1.6z"/></symbol>
    <symbol id="i-globe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3.2 3 14.8 0 18M12 3c-3 3.2-3 14.8 0 18"/></symbol>
    <symbol id="i-phone" viewBox="0 0 24 24"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></symbol>
    <symbol id="i-dots" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.3"/><circle cx="12" cy="12" r="1.3"/><circle cx="19" cy="12" r="1.3"/></symbol>
  </defs>
</svg>

<script>
(async function(){
'use strict';

var app=document.getElementById('app');
app.textContent='Загрузка данных Bitrix…';
var dateQuery=new URLSearchParams(location.search),dateFrom=document.getElementById('dateFrom'),dateTo=document.getElementById('dateTo');
var clock=new Date(),localToday=clock.getFullYear()+'-'+String(clock.getMonth()+1).padStart(2,'0')+'-'+String(clock.getDate()).padStart(2,'0');
dateFrom.value=dateQuery.get('from')||localToday.slice(0,7)+'-01';dateTo.value=dateQuery.get('to')||localToday;
function validateDates(){dateTo.setCustomValidity(dateFrom.value&&dateTo.value&&dateFrom.value>dateTo.value?'Дата «по» должна быть не раньше даты «с».':'')}
dateFrom.addEventListener('input',validateDates);dateTo.addEventListener('input',validateDates);
document.getElementById('dateRangeForm').addEventListener('submit',function(e){validateDates();if(!this.reportValidity()){e.preventDefault();return}var filters={reason:leadReason||'all',result:leadOutcome||'all',funnel:funnelChoice||'selected'};if(Array.isArray(selectedPipelines))filters.pipelines=selectedPipelines.join(',');Object.keys(filters).forEach(function(name){var input=this.querySelector('[name="'+name+'"]');if(!input){input=document.createElement('input');input.type='hidden';input.name=name;this.appendChild(input)}input.value=filters[name]},this)});
var live;
try { var response=await fetch('api.php?'+new URLSearchParams({from:dateFrom.value,to:dateTo.value}),{credentials:'same-origin',cache:'no-store'}); live=await response.json(); if(!response.ok||live.error)throw new Error(live.error||'Нет доступа к данным'); }
catch(error){app.textContent='Данные не загружены: '+error.message;return;}
var TODAY=live.today;
dateFrom.value=live.range.from;dateTo.value=live.range.to;
function inRange(range,date){return date>=range.from&&date<=range.to}
function P(){return {range:{from:live.range.from,to:live.range.to},prev:{from:live.range.previousFrom,to:live.range.previousTo},label:dRu(live.range.from)+' — '+dRu(live.range.to),chip:dRu(live.range.from)+' — '+dRu(live.range.to)}}
var PIPELINES=live.categories,selectedPipelines=PIPELINES.map(function(p){return p.id});
if(dateQuery.has('pipelines')){var requested=dateQuery.get('pipelines').split(',').filter(function(id){return /^\d+$/.test(id)}).map(Number);selectedPipelines=selectedPipelines.filter(function(id){return requested.indexOf(id)>=0})}
var categoryNames={};PIPELINES.forEach(function(p){categoryNames[p.id]=p.name});
var SOURCES=Object.keys(live.sources).map(function(id){var n=live.sources[id],v=n.toLowerCase(),ic=v.indexOf('instagram')>=0?'i-cam':v.indexOf('whatsapp')>=0?'i-chat':v.indexOf('тел')>=0?'i-phone':v.indexOf('сайт')>=0?'i-globe':'i-dots';return {id:id,name:n,ic:ic,bg:'#1668f2'}});
SOURCES.push({id:'__unknown',name:'Не указан',ic:'i-dots',bg:'#7c8799'});var SRC={};SOURCES.forEach(function(s){SRC[s.id]=s});
var EMP=live.employees.map(function(e){return {id:'e'+e.id,name:e.name,full:e.name,role:e.role,c:'#5b8fd6',sales:live.deals.some(function(d){return d.manager===e.id&&d.category!==7}),svc:live.deals.some(function(d){return d.manager===e.id&&d.category===7})}});
var EM={};EMP.forEach(function(e){EM[e.id]=e;e.ini=e.full.split(' ').map(function(w){return w[0]}).join('').slice(0,2).toUpperCase()});
var SALES_EMP=EMP.filter(function(e){return e.sales}).map(function(e){return e.id}),SVC_EMP=EMP.filter(function(e){return e.svc}).map(function(e){return e.id});
var SVC_ST={new:['Звонок/заявка','b-gray'],in_work:['В работе','b-amber'],done:['Запись на сервис','b-green'],cancel:['Провал','b-red']};
var DB={leads:[],service:[],tasks:live.tasks.map(function(t){return {id:'T'+t.id,title:t.title,who:'e'+t.manager,due:t.due,status:t.done?'done':'open',overdue:!!t.overdue}})};
var taskSummary=live.taskSummary;
function taskStats(id){return taskSummary.employees[id.slice(1)]||{total:0,overdue:0,done:0}}
function mapTask(t){var id='e'+t.manager;if(!EM[id]){var e={id:id,name:'Сотрудник '+t.manager,full:'Сотрудник '+t.manager,role:'',c:'#5b8fd6',ini:'С',sales:false,svc:false};EM[id]=e;EMP.push(e)}return {id:'T'+t.id,title:t.title,who:id,due:t.due,status:t.done?'done':'open',overdue:!!t.overdue}}
var taskCache={key:null,rows:[],summary:null,cursor:0,hasMore:false,loading:false,error:null};
function taskKey(q){return JSON.stringify([q.who||'',q.st||'',q.q||''])}
async function requestTaskPage(q,more){
 var key=taskKey(q);if(taskCache.loading&&taskCache.key===key)return;
 if(!more){taskCache={key:key,rows:[],summary:null,cursor:0,hasMore:false,loading:false,error:null}}
 var cache=taskCache;cache.loading=true;cache.error=null;
 try{var params=new URLSearchParams({tasks:'1',who:q.who||'',st:q.st||'',q:q.q||'',cursor:String(more?cache.cursor:0)});var response=await fetch('api.php?'+params.toString(),{credentials:'same-origin',cache:'no-store'});var page=await response.json();if(!response.ok||page.error)throw new Error(page.error||'Ошибка загрузки задач');if(taskCache!==cache)return;cache.rows=more?cache.rows.concat(page.tasks.map(mapTask)):page.tasks.map(mapTask);cache.summary=page.summary;cache.cursor=page.cursor;cache.hasMore=page.hasMore;}
 catch(error){if(taskCache===cache)cache.error=error.message}
 finally{if(taskCache===cache){cache.loading=false;render()}}
}
function ensureTaskPage(q){if(taskCache.key!==taskKey(q)){requestTaskPage(q,false);app.textContent='Загрузка задач…';return false}if(taskCache.loading){app.textContent='Загрузка задач…';return false}if(taskCache.error){app.textContent='Задачи не загружены: '+taskCache.error;return false}return !!taskCache.summary}
var stageKeys=[],STAGES=[],STAGE1=[];
var allDeals=live.deals.filter(function(d){return d.category!==7}).map(function(d){return {id:'D'+d.id,name:d.title,phone:categoryNames[d.category]||'—',src:SRC[d.source]?d.source:'__unknown',date:d.date,stageKey:d.category+':'+d.stageId,stageName:d.stageName,mgr:'e'+d.manager,model:categoryNames[d.category]||'—',exp:d.amount,amount:d.semantic==='S'?d.amount:0,won:d.semantic==='S',open:d.semantic==='P',failed:d.semantic==='F',stageId:d.stageId,category:d.category}});
var allService=live.deals.filter(function(d){return d.category===7}).map(function(d){return {id:'D'+d.id,client:d.title,car:'—',work:'—',status:d.semantic==='S'?'done':d.semantic==='F'?'cancel':d.stageId==='C7:NEW'?'new':'in_work',date:d.date,mgr:'e'+d.manager,wo:null,category:7}});
function rebuildStages(){
 stageKeys=[];STAGES=[];
 DB.leads.forEach(function(d){if(stageKeys.indexOf(d.stageKey)<0){stageKeys.push(d.stageKey);STAGES.push((selectedPipelines.filter(function(id){return id!==7}).length>1?(categoryNames[d.category]+' · '):'')+d.stageName)}d.stage=stageKeys.indexOf(d.stageKey)});
 STAGE1=STAGES;
}
DB.leads=allDeals.filter(function(d){return selectedPipelines.indexOf(d.category)>=0});DB.service=allService.filter(function(d){return selectedPipelines.indexOf(d.category)>=0});rebuildStages();document.getElementById('pipelineLbl').textContent=selectedPipelines.length===PIPELINES.length?'Все воронки':'Воронки: '+selectedPipelines.length;

/* ================= HELPERS ================= */
function esc(s){return String(s==null?'':s).replace(/[&<>"']/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]})}
function fmt(n){return n.toLocaleString('ru-RU')}
function mln(v){return fmt(Math.round(v/1e6))}
function mln1(v){return (v/1e6).toFixed(1).replace('.',',')+' млн ₸'}
function pct(v){return v>=10?Math.round(v)+'%':v.toFixed(1).replace('.',',')+'%'}
function dRu(d){if(!d)return 'Без срока';var p=d.split('-');return p[2]+'.'+p[1]+'.'+p[0]}
function ico(id,cls){return '<svg class="i '+(cls||'')+'"><use href="#'+id+'"/></svg>'}
function srcCell(id){var s=SRC[id];return '<span class="src"><span class="sico" style="background:'+s.bg+'"><svg><use href="#'+s.ic+'"/></svg></span>'+esc(s.name)+'</span>'}
function ava(e,cls){return '<span class="ava '+(cls||'sm')+'" style="background:'+e.c+'">'+esc(e.ini)+'</span>'}
function personCell(id){var e=EM[id];return '<span class="person">'+ava(e)+esc(e.name)+'</span>'}
function isOverdue(t){return t.overdue}
function stageBadge(s){return '<span class="badge b-blue">'+esc(STAGE1[s]||'—')+'</span>'}

function inM(range){return function(r){return inRange(range,r.date)}}

function metrics(range){
  if(!range)return null;
  var f=inM(range),leads=DB.leads.filter(f),svc=DB.service.filter(f);
  var st=STAGES.map(function(_,k){return leads.filter(function(l){return l.stage===k}).length});
  var sold=leads.filter(function(l){return l.won});
  var src=SOURCES.map(function(s){var ls=leads.filter(function(l){return l.src===s.id});
    return {s:s,leads:ls.length,deals:ls.filter(function(l){return l.open}).length,sales:ls.filter(function(l){return l.won}).length,rev:ls.reduce(function(a,l){return a+(l.amount||0)},0)}});
  return {leads:leads.length,stages:st,sales:sold.length,deals:leads.filter(function(l){return l.open}).length,rev:sold.reduce(function(a,l){return a+l.amount},0),src:src,
    svcReq:svc.length,svcDone:svc.filter(function(s){return s.status==='done'}).length,wo:svc.filter(function(s){return s.status==='done'}).length};
}
function delta(cur,prev,invert){
  if(prev==null)return '<span class="delta na">нет данных</span>';
  if(prev===0&&cur!==0)return '<span class="delta na">Нет базы сравнения</span>';
  var p=prev?Math.trunc((cur-prev)/prev*100):0,up=p>=0;
  if(p===0)return '<span class="delta na">0% без изменений</span>';
  var cls=invert?(cur>prev?'bad':'up'):(up?'up':'dn');
  return '<span class="delta '+cls+'">'+(up?'↑ +':'↓ ')+p+'%</span>';
}

/* ================= HERO ILLUSTRATION ================= */
function heroSvg(){
  var cars='',cols=['#1d2430','#e9edf2','#6b7686','#23324a','#aab4c2','#12161d','#3a5a8c','#d7dde5','#1d2430','#8894a4'];
  for(var i=0;i<10;i++){var x=150+i*47+(i%2?6:0),y=128+(i%3)*4,c=cols[i],s=0.78+(i%3)*0.06;
    cars+='<g transform="translate('+x+' '+y+') scale('+s+')"><path d="M2 16Q2 11 8 10L16 9 22 3Q24 2 28 2H40Q44 2 47 6L50 9 56 10Q59 11 59 15V17H2Z" fill="'+c+'"/><path d="M23 9 27 4H38L42 9Z" fill="#9fb3cc" opacity=".8"/><circle cx="14" cy="17" r="4" fill="#0e1116"/><circle cx="47" cy="17" r="4" fill="#0e1116"/><circle cx="14" cy="17" r="1.6" fill="#8a94a3"/><circle cx="47" cy="17" r="1.6" fill="#8a94a3"/></g>';}
  var grid='';for(var gx=190;gx<600;gx+=22)grid+='<line x1="'+gx+'" y1="62" x2="'+gx+'" y2="128" stroke="#dfe7f1" stroke-width="1"/>';
  return '<svg viewBox="0 0 640 165" preserveAspectRatio="xMaxYMax slice"><defs><linearGradient id="sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#dbe5f1"/><stop offset="1" stop-color="#f2f5f9"/></linearGradient><linearGradient id="gl" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#7f97b5"/><stop offset=".55" stop-color="#b8c8dc"/><stop offset="1" stop-color="#e3eaf3"/></linearGradient></defs>'+
  '<rect width="640" height="165" fill="url(#sky)"/>'+
  '<path d="M40 110 90 96 150 100 150 128 40 128Z" fill="#c5d0dd"/>'+
  '<rect x="170" y="62" width="470" height="66" fill="url(#gl)"/>'+grid+
  '<path d="M160 52H640V64H172Z" fill="#eef2f6"/><path d="M160 64H640V67H172Z" fill="#9aa9bb"/>'+
  '<rect x="470" y="30" width="170" height="24" fill="#f4f6f9"/><rect x="480" y="36" width="70" height="10" rx="2" fill="#c6d2e0"/>'+
  '<rect x="360" y="84" width="46" height="44" fill="#5f7896" opacity=".6"/>'+
  '<rect x="0" y="128" width="640" height="37" fill="#e6ebf1"/><path d="M0 146H640" stroke="#d3dbe5" stroke-width="2"/>'+cars+'</svg>';
}

/* ================= ROUTER ================= */
var app=document.getElementById('app');
function parseHash(){var h=location.hash.replace(/^#\/?/,'');var q={},parts=h.split('?');(parts[1]||'').split('&').forEach(function(kv){if(!kv)return;var a=kv.split('=');q[a[0]]=decodeURIComponent(a[1]||'')});return {path:parts[0].split('/').filter(Boolean),q:q}}
function go(h){if(location.hash===h)render();else location.hash=h}
window.addEventListener('hashchange',function(){render();window.scrollTo(0,0)});

function render(){
  if(window.BAVARIA_APP_READY&&window.BX24){requestAnimationFrame(function(){BX24.fitWindow()})}
  var r=parseHash(),p=r.path;
  if(!p.length)return renderDash();
  if(p[0]==='employee')return renderEmployee(p[1]);
  if(p[0]==='staff')return renderStaff();
  if(LISTS[p[0]])return renderList(p[0],r.q);
  renderDash();
}

/* ================= DASHBOARD ================= */
function donut(val,max,size,stroke,label,big){
  var r=(size-stroke)/2,C=2*Math.PI*r,f=max?Math.min(val/max,1):0;
  return '<div class="donut" style="width:'+size+'px;height:'+size+'px"><svg width="'+size+'" height="'+size+'" viewBox="0 0 '+size+' '+size+'" style="transform:rotate(-90deg)"><circle cx="'+size/2+'" cy="'+size/2+'" r="'+r+'" fill="none" stroke="var(--track)" stroke-width="'+stroke+'"/><circle class="arc" cx="'+size/2+'" cy="'+size/2+'" r="'+r+'" fill="none" stroke="var(--blue)" stroke-width="'+stroke+'" stroke-dasharray="'+(C*f)+' '+C+'"/></svg><div class="dl"><b>'+big+'</b><span>'+label+'</span></div></div>';
}
function lerp(a,b,t){var pa=parseInt(a.slice(1),16),pb=parseInt(b.slice(1),16);var c=[16,8,0].map(function(s){var x=(pa>>s)&255,y=(pb>>s)&255;return Math.round(x+(y-x)*t)});return 'rgb('+c.join(',')+')'}
function chip(){return '<span class="chip date-chip">'+esc(P().chip)+'</span>'}

var funnelChoice=dateQuery.get('funnel')||(PIPELINES.some(function(p){return p.id===0})?'0':'selected'),funnelExpanded=false;
var MAIN_STAGE_LABELS={'новая сделка':'Новая сделка','анализ потребностей':'Потребности','коммерческое предложение':'Предложение','тест-драйв':'Тест-драйв','согласование условий':'Условия','принятие решения о покупке':'Решение','резерв/заказ авто':'Резерв / заказ','счет на оплату':'Счёт на оплату','подписание дкп':'Договор','подготовка к выдаче':'Подготовка к выдаче','отложил покупку':'Отложено'};
function dealStageVisits(deal){return (live.stageHistory||{})[deal.id.slice(1)]||{}}
function funnelLayout(category,configured){
 var semantics=(live.stageSemantics||{})[category]||{},names=(live.stageNames||{})[category]||{};
 var process=configured.map(String).filter(function(id){return semantics[id]==='P'});
 var branches=process.filter(function(id){var name=(names[id]||'').trim().toLowerCase();return (Number(category)===0&&id==='12')||['отложено','отложил покупку','отложенная покупка'].indexOf(name)>=0});
 return {process:process.filter(function(id){return branches.indexOf(id)<0}),branches:branches,names:names,semantics:semantics};
}
function cumulativeDealRank(deal,layout){
 var visits=dealStageVisits(deal),known=Object.keys(visits),current=String(deal.stageId||'');
 if(current)known.push(current);
 var succeeded=!!deal.won||known.some(function(id){return layout.semantics[id]==='S'});
 if(succeeded)return layout.process.length-1;
 return known.reduce(function(rank,id){return Math.max(rank,layout.process.indexOf(id))},0);
}
function dealReachedCumulativeStage(deal,category,stage){
 var layout=funnelLayout(category,((live.stageOrder||{})[category]||[])),index=layout.process.indexOf(stage);
 return index>=0&&cumulativeDealRank(deal,layout)>=index;
}
function dealPassedCumulativeTransition(deal,category,from,to){
 var layout=funnelLayout(category,((live.stageOrder||{})[category]||[])),index=layout.process.indexOf(to);
 return index>0&&layout.process[index-1]===from&&cumulativeDealRank(deal,layout)>=index;
}
function funnelPercent(value){return value.toLocaleString('ru-RU',{maximumFractionDigits:1})+'%'}
function historyFunnel(rows,category,configured){
 var seen=new Set();rows=rows.filter(function(deal){if(seen.has(deal.id))return false;seen.add(deal.id);return true});
 var layout=funnelLayout(category,configured),process=layout.process,names=layout.names;
 var ranks=rows.map(function(deal){return cumulativeDealRank(deal,layout)});
 var coverage=rows.filter(function(d){return Object.keys(dealStageVisits(d)).length>0}).length;
 var counts=process.map(function(id,index){return ranks.filter(function(rank){return rank>=index}).length});
 var stages=process.map(function(id,index){return {id:id,name:names[id]||id,count:counts[index],previous:index?process[index-1]:null,previousName:index?(names[process[index-1]]||process[index-1]):null,converted:counts[index],denominator:index?counts[index-1]:0}});
 var branches=layout.branches.map(function(id){return {id:id,name:names[id]||id,count:rows.filter(function(deal){return String(deal.stageId)===id}).length}});
 return {stages:stages,coverage:coverage,branches:branches};
}

function renderFunnel(range){
 var available=PIPELINES.filter(function(p){return p.id!==7&&selectedPipelines.indexOf(p.id)>=0});
 if(funnelChoice!=='selected'&&!available.some(function(p){return String(p.id)===funnelChoice}))funnelChoice='selected';
 var ids=funnelChoice==='selected'?available.map(function(p){return p.id}):[Number(funnelChoice)];
 var rows=DB.leads.filter(function(d){return ids.indexOf(d.category)>=0&&inRange(range,d.date)});
 var picker='<div class="funnel-picker"><select class="inp" data-funnel-select aria-label="Воронка для блока продаж"><option value="selected"'+(funnelChoice==='selected'?' selected':'')+'>Выбранные воронки</option>'+available.map(function(p){return '<option value="'+p.id+'"'+(funnelChoice===String(p.id)?' selected':'')+'>'+esc(p.name)+'</option>'}).join('')+'</select></div>';
 var head='<div class="ph">'+ico('i-users')+'<div><h2>Воронка продаж</h2><p>Накопительная конверсия</p></div>'+chip()+'</div>'+picker;
 if(!ids.length||!rows.length)return head+'<div class="empty">Нет сделок за выбранный период</div>';
 if(ids.length>1){
  var summary=available.map(function(p){var deals=rows.filter(function(d){return d.category===p.id});if(!deals.length)return '';return '<tr><td><button class="funnel-link" data-funnel-pick="'+p.id+'" title="'+esc(p.name)+'">'+esc(p.name)+'</button></td><td class="num">'+deals.filter(function(d){return d.open}).length+'</td><td class="num">'+deals.filter(function(d){return d.won}).length+'</td><td class="num">'+deals.filter(function(d){return d.failed}).length+'</td></tr>'}).join('');
  return head+'<div class="tw"><table class="funnel-overview"><thead><tr><th>Воронка</th><th class="num">В работе</th><th class="num">Успешные</th><th class="num">Проваленные</th></tr></thead><tbody>'+summary+'</tbody></table></div>';
 }
 var category=ids[0],won=rows.filter(function(d){return d.won}).length,failed=rows.filter(function(d){return d.failed}).length;
 var configured=live.stageOrder&&live.stageOrder[category]?live.stageOrder[category].map(String):category===0?['NEW','6','1','13','11','5','10','4','7','2','12']:[];
 var history=historyFunnel(rows,category,configured),stages=history.stages;
 var max=Math.max.apply(null,stages.map(function(stage){return stage.count}).concat([1]));
 var transitions=stages,displayed=funnelExpanded?transitions:transitions.slice(0,6);
 var bars=displayed.map(function(stage,i){
  var shortLabel=function(name){return category===0?(MAIN_STAGE_LABELS[name.toLowerCase()]||name):name};
  var label=stage.previous?shortLabel(stage.previousName)+' → '+shortLabel(stage.name):shortLabel(stage.name);
  var rate=stage.previous&&stage.denominator?funnelPercent(stage.converted/stage.denominator*100):'—';
  var description=stage.previous?stage.previousName+' → '+stage.name+': '+stage.converted+' из '+stage.denominator:stage.name+': всего '+stage.count+' сделок';
  var link=stage.previous?'transitionFrom='+encodeURIComponent(stage.previous)+'&transitionTo='+encodeURIComponent(stage.id):'cumulativeStage='+encodeURIComponent(stage.id);
  var width=max?stage.count/max*100:0;
  return '<div class="frow transition-row" data-go="#/leads?category='+category+'&'+link+'" title="'+esc(description)+'"><span class="fn">'+esc(label)+'</span><span class="fb"><i style="width:'+width+'%;background:'+lerp('#0b4db5','#9dc3ff',i/Math.max(displayed.length-1,1))+'"></i></span><span class="fc">'+(stage.previous?fmt(stage.converted)+'/'+fmt(stage.denominator):fmt(stage.count))+'</span><span class="fp">'+rate+'</span></div>';
 }).join('');
 bars='<div class="funnel-columns"><span>Этап / переход</span><span></span><span>Прошли</span><span>Конверсия</span></div>'+bars;
 var note='Пропущенные основные этапы засчитываются до максимального достигнутого. Каждая сделка — один раз. История есть у '+fmt(history.coverage)+' из '+fmt(rows.length)+' сделок.';
 if(live.stageHistoryError){bars='<div class="empty">'+esc(live.stageHistoryError)+'</div>';note='Переходы не восстановлены; итоговые статусы показаны по текущим данным.'}
 else if(!transitions.length){bars='<div class="empty">Не удалось определить порядок рабочих этапов.</div>'}
 var more=!live.stageHistoryError&&transitions.length>6?'<button class="btn btn-g btn-s funnel-more" data-funnel-expand aria-expanded="'+funnelExpanded+'">'+(funnelExpanded?'Свернуть':'Ещё стадий: '+(transitions.length-6))+'</button>':'';
 var outcomes='<div class="funnel-outcomes"><button class="funnel-outcome success" data-go="#/leads?category='+category+'&outcome=won"><span>Успешные</span><b>'+fmt(won)+'</b></button><button class="funnel-outcome failed" data-go="#/leads?category='+category+'&outcome=failed"><span>Проваленные</span><b>'+fmt(failed)+'</b></button></div>';
 var branchCards=history.branches.length?'<div class="funnel-branches">'+history.branches.map(function(branch){return '<button class="funnel-branch" data-go="#/leads?category='+category+'&sideStage='+encodeURIComponent(branch.id)+'" title="'+esc(branch.name)+'"><span>Отложено</span><b>'+fmt(branch.count)+'</b></button>'}).join('')+'</div>':'';
 return head+'<div class="fun-wrap"><div class="funnel">'+bars+more+'</div>'+donut(won,rows.length,150,16,'Доля<br>успешных',pct(won/rows.length*100))+'</div>'+outcomes+branchCards+'<p class="funnel-history-note">'+esc(note)+'</p>';
}

var leadReason=dateQuery.get('reason')||'all',leadOutcome=['all','qualified','successful','failed','open','unconfirmed'].indexOf(dateQuery.get('result'))>=0?dateQuery.get('result'):'all';
function renderLeadChart(){
 var stats=live.leadStats;
 var header='<div class="ph">'+ico('i-users')+'<div><h2>Обращения из лидов</h2><p>Все причины обращения и результаты лидов</p></div>'+chip()+'</div>';
 if(!stats||stats.error)return header+'<div class="empty">'+esc(stats&&stats.error||'Данные лидов недоступны')+'</div>';
 var reasons=stats.reasons||{},keys=Object.keys(reasons);
 if(!Object.prototype.hasOwnProperty.call(reasons,leadReason))leadReason='all';
 var outcomes=[['qualified','Целевые'],['successful','Успешные'],['failed','Неуспешные'],['open','В работе'],['unconfirmed','Не подтверждено']];
 var controls='<div class="lead-filters"><label>Причина обращения<select class="inp" data-lead-reason><option value="all">Все причины</option>'+keys.map(function(id){return '<option value="'+esc(id)+'"'+(leadReason===id?' selected':'')+'>'+esc(reasons[id].label)+'</option>'}).join('')+'</select></label><label>Результат<select class="inp" data-lead-outcome><option value="all">Все результаты</option>'+outcomes.map(function(o){return '<option value="'+o[0]+'"'+(leadOutcome===o[0]?' selected':'')+'>'+o[1]+'</option>'}).join('')+'</select></label></div>';
 var total=0;
 var chart=keys.filter(function(id){return leadReason==='all'||id===leadReason}).map(function(id){
  var d=stats.directions[id],known=reasons[id].category!==null;
  var rowOutcomes=outcomes.filter(function(o){return known?o[0]!=='successful':o[0]!=='qualified'&&o[0]!=='unconfirmed'});
  var visible=rowOutcomes.filter(function(o){return leadOutcome==='all'||o[0]===leadOutcome});
  total+=d.total;
  var summary='Всего '+fmt(d.total)+(known?' · целевых '+fmt(d.qualified):' · успешных '+fmt(d.successful))+' · '+(d.total?pct((known?d.qualified:d.successful)/d.total*100):'—');
  return '<div class="lead-chart-row"><div class="lead-chart-title"><b>'+esc(reasons[id].label)+'</b><span>'+summary+'</span></div><div class="lead-stacked" role="img" aria-label="'+esc(reasons[id].label+': '+summary)+'">'+visible.map(function(o){return d[o[0]]?'<div class="lead-segment '+o[0]+'" style="width:'+d[o[0]]/Math.max(d.total,1)*100+'%" title="'+o[1]+': '+fmt(d[o[0]])+'"><span>'+fmt(d[o[0]])+'</span></div>':''}).join('')+'</div><div class="lead-row-counts">'+visible.map(function(o){return '<span><i class="lead-dot '+o[0]+'"></i>'+o[1]+' <b>'+fmt(d[o[0]])+'</b></span>'}).join('')+'</div></div>';
 }).join('');
 return header+controls+'<div class="lead-chart">'+chart+'</div><div class="lead-totals"><span>Всего обращений <b>'+fmt(total)+'</b></span></div><p class="lead-note">Все варианты списка, включая причины без обращений. Период — дата создания лида. Для новых авто и сервиса конверсия — доля целевых со сделкой нужной воронки; не подтверждено — успешный лид без доступной связи с ней. Для остальных причин — доля успешных лидов по статусу, без проверки воронки. Лиды в работе не считаются отказами.'+(stats.unassigned?' Без однозначной причины обращения: '+fmt(stats.unassigned)+'.':'')+'</p>';
}

function renderDash(){
  var per=P(),m=metrics(per.range),pv=metrics(per.prev);
  var overdue=taskSummary.overdue;
  var K=[
    {ic:'i-coins',lb:'Сумма успешных сделок',v:mln(m.rev),u:'млн ₸',d:delta(m.rev,pv&&pv.rev),go:'#/sales'},
    {ic:'i-car',lb:'Успешные сделки',v:fmt(m.sales),d:delta(m.sales,pv&&pv.sales),go:'#/sales'},
    {ic:'i-wrench',lb:'Сервис<br>(записи)',v:fmt(m.wo),d:delta(m.wo,pv&&pv.wo),go:'#/service?wo=1'},
    {ic:'i-users',lb:'Новые сделки',v:fmt(m.leads),d:delta(m.leads,pv&&pv.leads),go:'#/leads'},
    {ic:'i-hand',lb:'Активные сделки',v:fmt(m.deals),d:delta(m.deals,pv&&pv.deals),go:'#/deals'},
    {ic:'i-bell',lb:'Просроченные<br>задачи',v:fmt(overdue),d:'<span class="delta na">Текущее состояние</span>',go:'#/tasks?st=overdue',warn:1}];
  var kh=K.map(function(k){return '<button class="card kpi'+(k.warn?' warn':'')+'" data-go="'+k.go+'"><div class="ic">'+ico(k.ic)+'</div><div style="min-width:0"><div class="lb">'+k.lb+'</div><div class="val">'+k.v+(k.u?'<small>'+k.u+'</small>':'')+'</div>'+k.d+'<div class="cmp">к предыдущему периоду</div></div></button>'}).join('');

  var sconv=m.svcReq?m.svcDone/m.svcReq*100:0;

  var sh=m.src.filter(function(r){return r.leads!==0||r.deals!==0||r.sales!==0||r.rev!==0}).map(function(r){return '<tr class="cl" data-go="#/leads?source='+r.s.id+'"><td>'+srcCell(r.s.id)+'</td><td class="num">'+r.leads+'</td><td class="num">'+r.deals+'</td><td class="num">'+r.sales+'</td><td class="r" style="white-space:nowrap">'+mln(r.rev)+' млн ₸</td></tr>'}).join('')||'<tr><td colspan="5" class="empty">Нет данных за выбранный период</td></tr>';

  var od=EMP.map(function(e){var t=taskStats(e.id);return {e:e,total:t.total,od:t.overdue}}).filter(function(x){return x.od>0}).sort(function(a,b){return b.od-a.od||b.total-a.total});
  var oh=od.length?od.map(function(x){return '<tr class="cl" data-go="#/employee/'+x.e.id+'"><td>'+personCell(x.e.id)+'</td><td>'+esc(x.e.role)+'</td><td class="num">'+x.total+'</td><td class="num red">'+x.od+'</td></tr>'}).join(''):'<tr><td colspan="4" class="empty">Просроченных задач нет 🎉</td></tr>';

  app.innerHTML=
  '<section class="hero"><div class="hero-img">'+heroSvg()+'</div><div class="hero-txt"><h1>Панель управления</h1><nav><a data-go="#/sales">Продажи</a><span>·</span><a data-go="#/service">Сервис</a><span>·</span><a data-go="#/leads">Клиенты</a><span>·</span><a data-go="#/tasks">Задачи</a><span>·</span><a data-go="#/staff">Эффективность</a></nav></div></section>'+
  '<section class="kpis">'+kh+'</section>'+
  '<section class="card panel lead-panel">'+renderLeadChart()+'</section>'+
  '<section class="mid">'+
   '<div class="card panel p-fun">'+renderFunnel(per.range)+'</div>'+
   '<div class="card panel"><div class="ph">'+ico('i-car')+'<div><h2>Сервис</h2><p>Доля заявок, завершившихся записью</p></div>'+chip()+'</div><div class="svc">'+donut(m.svcDone,m.svcReq,170,20,'Доля<br>успешных',Math.round(sconv)+'%')+
     '<div class="svc-stats"><div class="stat" data-go="#/service"><span>Обращения в сервис</span><b>'+fmt(m.svcReq)+'</b></div><div class="stat" data-go="#/service?st=done"><span>Записи на сервис</span><b>'+fmt(m.svcDone)+'</b></div><div class="svc-bar"><i style="width:'+sconv+'%"></i></div><div class="stat" data-go="#/service?st=in_work"><span>В работе сейчас</span><b>'+fmt(DB.service.filter(function(s){return s.status==='in_work'&&inRange(per.range,s.date)}).length)+'</b></div></div></div></div>'+
   '<div class="card panel"><div class="ph">'+ico('i-bars')+'<h2>Источники клиентов</h2>'+chip()+'</div><div class="tw"><table><thead><tr><th>Источник</th><th class="num">Сделки</th><th class="num">В работе</th><th class="num">Успешные</th><th class="r">Сумма успешных сделок</th></tr></thead><tbody>'+sh+'</tbody></table></div></div>'+
  '</section>'+
  '<section class="card tasks-card"><div class="ph">'+ico('i-clock')+'<h2>Просроченные задачи</h2></div><div class="tw"><table><thead><tr><th>Сотрудник</th><th>Должность</th><th class="num">Всего задач</th><th class="num">Просрочено</th></tr></thead><tbody>'+oh+'</tbody></table></div></section>';
}

/* ================= LIST PAGES ================= */
var LISTS={
 leads:{title:'Обращения и сделки',crumb:'Клиенты',create:'lead',
   rows:function(q){var ms=P().range;return DB.leads.filter(function(l){return inRange(ms,l.date)&&(q.category!==undefined?l.category===Number(q.category):true)&&(q.outcome==='won'?l.won:q.outcome==='failed'?l.failed:true)&&(q.transitionFrom&&q.transitionTo?dealPassedCumulativeTransition(l,l.category,q.transitionFrom,q.transitionTo):true)&&(q.cumulativeStage?dealReachedCumulativeStage(l,l.category,q.cumulativeStage):true)&&(q.sideStage?l.stageId===q.sideStage:true)&&(q.historyStage?!!dealStageVisits(l)[q.historyStage]:true)&&(q.source?l.src===q.source:true)&&(q.stage!==undefined&&q.stage!==''?l.stage===+q.stage:true)})},
   filters:[{k:'source',lb:'Все источники',opts:SOURCES.map(function(s){return [s.id,s.name]})},{k:'stage',lb:'Все этапы',opts:STAGES.map(function(s,i){return [String(i),'Стадия: '+s]})}],
   head:['Сделка','Воронка','Источник','Этап','Менеджер','Дата'],
   row:function(l){return '<tr class="cl" data-lead="'+l.id+'"><td><b>'+esc(l.name)+'</b></td><td style="white-space:nowrap">'+esc(l.phone)+'</td><td>'+srcCell(l.src)+'</td><td>'+stageBadge(l.stage)+'</td><td>'+personCell(l.mgr)+'</td><td>'+dRu(l.date)+'</td></tr>'},
   text:function(l){return l.name+' '+l.phone}},
 deals:{title:'Сделки',crumb:'Продажи › Сделки',create:'lead',
   rows:function(q){var ms=P().range;return DB.leads.filter(function(l){return inRange(ms,l.date)&&l.open&&(q.source?l.src===q.source:true)})},
   filters:[{k:'source',lb:'Все источники',opts:SOURCES.map(function(s){return [s.id,s.name]})}],
   head:['Название','Воронка','Статус','Сумма','Менеджер','Источник'],
   row:function(l){return '<tr class="cl" data-lead="'+l.id+'"><td><b>'+esc(l.name)+'</b></td><td>'+esc(l.model||'—')+'</td><td>'+(l.won?'<span class="badge b-green">Успешна</span>':'<span class="badge b-amber">В работе</span>')+'</td><td style="white-space:nowrap">'+mln1(l.amount||l.exp||0)+'</td><td>'+personCell(l.mgr)+'</td><td>'+srcCell(l.src)+'</td></tr>'},
   text:function(l){return l.name+' '+(l.model||'')},
   sum:function(r){var w=r.filter(function(l){return l.open});return [['Сделок',fmt(r.length)],['В работе',fmt(w.length)],['Потенциал в работе',mln1(w.reduce(function(a,l){return a+(l.exp||0)},0))]]}},
 sales:{title:'Успешные сделки',crumb:'Продажи',create:'lead',
   rows:function(q){var ms=P().range;return DB.leads.filter(function(l){return inRange(ms,l.date)&&l.won&&(q.source?l.src===q.source:true)&&(q.mgr?l.mgr===q.mgr:true)})},
   filters:[{k:'source',lb:'Все источники',opts:SOURCES.map(function(s){return [s.id,s.name]})},{k:'mgr',lb:'Все менеджеры',opts:SALES_EMP.map(function(id){return [id,EM[id].name]})}],
   head:['Название','Воронка','Сумма','Менеджер','Источник','Дата'],
   row:function(l){return '<tr class="cl" data-lead="'+l.id+'"><td><b>'+esc(l.name)+'</b></td><td>'+esc(l.model)+'</td><td style="white-space:nowrap"><b>'+mln1(l.amount)+'</b></td><td>'+personCell(l.mgr)+'</td><td>'+srcCell(l.src)+'</td><td>'+dRu(l.date)+'</td></tr>'},
   text:function(l){return l.name+' '+l.model},
   sum:function(r){var s=r.reduce(function(a,l){return a+l.amount},0);return [['Успешных сделок',fmt(r.length)],['Сумма успешных сделок',mln(s)+' млн ₸'],['Средний чек',r.length?mln1(s/r.length):'—']]}},
 service:{title:'Сервис',crumb:'Сервис',create:'service',
   rows:function(q){var ms=P().range;return DB.service.filter(function(s){return inRange(ms,s.date)&&(q.st?s.status===q.st:true)&&(q.wo?s.status==='done':true)&&(q.mgr?s.mgr===q.mgr:true)})},
   filters:[{k:'st',lb:'Все статусы',opts:Object.keys(SVC_ST).map(function(k){return [k,SVC_ST[k][0]]})},{k:'mgr',lb:'Все ответственные',opts:SVC_EMP.map(function(id){return [id,EM[id].name]})}],
   head:['Заявка','Автомобиль','Вид работ','Заказ-наряд','Статус','Ответственный','Дата'],
   row:function(s){return '<tr class="cl" data-svc="'+s.id+'"><td><b>'+esc(s.client)+'</b></td><td style="white-space:nowrap">'+esc(s.car)+'</td><td>'+esc(s.work)+'</td><td>'+(s.wo||'—')+'</td><td><span class="badge '+SVC_ST[s.status][1]+'">'+SVC_ST[s.status][0]+'</span></td><td>'+personCell(s.mgr)+'</td><td>'+dRu(s.date)+'</td></tr>'},
   text:function(s){return s.client+' '+s.car+' '+s.work+' '+(s.wo||'')},
   sum:function(r){var d=r.filter(function(s){return s.status==='done'}).length;return [['Обращений',fmt(r.length)],['Записей',fmt(d)]]}},
 tasks:{title:'Задачи',crumb:'Задачи',create:'task',noPeriod:1,
   rows:function(q){return taskCache.rows},
   filters:[{k:'st',lb:'Все задачи',opts:[['overdue','Просроченные'],['open','В срок'],['done','Выполненные']]},{k:'who',lb:'Все сотрудники',opts:EMP.map(function(e){return [e.id,e.name]})}],
   head:['','Задача','Исполнитель','Срок','Статус'],
   row:taskRow,text:function(t){return t.title+' '+EM[t.who].name},
   sum:function(r){return [['Всего',fmt(r.length)],['Просрочено',fmt(r.filter(isOverdue).length)],['Выполнено',fmt(r.filter(function(t){return t.status==='done'}).length)]]}}
};
function taskRow(t){var o=isOverdue(t),d=t.status==='done';
  return '<tr><td style="width:36px"><span class="ck'+(d?' on':'')+'" role="img" aria-label="'+(d?'Задача выполнена':'Задача в работе')+'"></span></td><td class="'+(d?'done-t':'')+'">'+esc(t.title)+'</td><td>'+personCell(t.who)+'</td><td class="'+(o?'red':'')+'" style="white-space:nowrap">'+dRu(t.due)+'</td><td>'+(d?'<span class="badge b-green">Выполнена</span>':o?'<span class="badge b-red">Просрочена</span>':'<span class="badge b-blue">В работе</span>')+'</td></tr>'}

var activityCache={key:null,loading:false,data:null,error:null},activityWho='all';
async function requestActivityReport(){
 var key=selectedPipelines.join(',');if(activityCache.key===key&&activityCache.loading)return;
 var cache={key:key,loading:true,data:null,error:null};activityCache=cache;
 try{var response=await fetch('api.php?'+new URLSearchParams({activities:'1',categories:key}),{credentials:'same-origin',cache:'no-store'});var data=await response.json();if(!response.ok||data.error)throw new Error(data.error||'Ошибка загрузки CRM-дел');if(activityCache===cache)cache.data=data;}
 catch(error){if(activityCache===cache)cache.error=error.message}
 finally{if(activityCache===cache){cache.loading=false;render()}}
}
function renderActivityReport(){
 var heading='<div class="ph">'+ico('i-clock')+'<div><h2>Сводный отчёт по делам</h2><p>Незавершённые CRM-дела по активным сделкам выбранных воронок</p></div></div>';
 if(activityCache.key!==selectedPipelines.join(',')){requestActivityReport();return '<section class="card panel activity-report">'+heading+'<p>Загрузка CRM-дел…</p></section>'}
 if(activityCache.loading)return '<section class="card panel activity-report">'+heading+'<p>Загрузка CRM-дел…</p></section>';
 if(activityCache.error)return '<section class="card panel activity-report">'+heading+'<p>'+esc(activityCache.error)+'</p><button class="btn btn-g btn-s" data-activity-refresh>Повторить</button></section>';
 var report=activityCache.data;if(!report)return '';
 var columns=[['overdue','Проср.','Срок до сегодняшнего дня'],['today','Сегодня','Срок сегодня'],['tomorrow','Завтра','Срок завтра'],['days3','2–3 дня','От послезавтра до третьего дня включительно'],['days7','4–7 дней','С четвёртого по седьмой день включительно'],['without','Сделки без дел','Активные сделки без доступных незавершённых CRM-дел'],['active','Активные','Активные сделки по ответственному сделки'],['activities','Дела','Все доступные незавершённые CRM-дела по ответственному дела']];
 if(activityWho!=='all'&&!report.rows.some(function(row){return String(row.id)===activityWho}))activityWho='all';
 var rows=report.rows.filter(function(row){return activityWho==='all'||String(row.id)===activityWho}),totals={};columns.forEach(function(c){totals[c[0]]=0});
 function cells(counts){return columns.map(function(c){var value=counts[c[0]];return '<td class="num '+(!value?'activity-zero':(['overdue','today','days3','without'].indexOf(c[0])>=0?'activity-risk':['days7'].indexOf(c[0])>=0?'activity-warn':''))+'">'+fmt(value)+'</td>'}).join('')}
 var body=rows.map(function(row){columns.forEach(function(c){totals[c[0]]+=row.counts[c[0]]});return '<tr><td><b>'+esc(row.name)+'</b></td>'+cells(row.counts)+'</tr>'}).join('')||'<tr><td class="empty" colspan="9">Нет активных сделок и незавершённых дел</td></tr>';
 var controls='<div class="activity-controls"><label>Ответственный<select class="inp" data-activity-who><option value="all">Все ответственные</option>'+report.rows.map(function(row){return '<option value="'+row.id+'"'+(activityWho===String(row.id)?' selected':'')+'>'+esc(row.name)+'</option>'}).join('')+'</select></label><span>На '+dRu(report.asOf)+' · Ответственных: '+rows.length+'</span><button class="btn btn-g btn-s" data-activity-refresh>Обновить</button></div>';
 return '<section class="card panel activity-report">'+heading+controls+'<div class="tw"><table><thead><tr><th>Ответственный</th>'+columns.map(function(c){return '<th class="num" title="'+esc(c[2])+'">'+c[1]+'</th>'}).join('')+'</tr></thead><tbody>'+body+'</tbody><tfoot><tr><td><b>Итого</b></td>'+cells(totals)+'</tr></tfoot></table></div><p class="activity-note">Текущее состояние, независимо от периода выше. Интервалы сроков не пересекаются. В «Дела» также входят дела без срока и со сроком позже семи дней. Сделки считаются по ответственному сделки, дела — по ответственному дела.</p></section>';
}

var listState={limit:50};
function renderList(kind,q){
  var activityReportHtml=kind==='tasks'?renderActivityReport():'';
  if(kind==='tasks'&&!ensureTaskPage(q))return;
  var L=LISTS[kind],rows=kind==='tasks'?taskCache.rows:L.rows(q),search=kind==='tasks'?'':(q.q||'').toLowerCase();
  if(search)rows=rows.filter(function(r){return L.text(r).toLowerCase().indexOf(search)>=0});
  var historyNote=kind==='leads'&&q.historyStage?'<p class="funnel-history-note">Сделки, побывавшие на этапе «'+esc(((live.stageNames||{})[q.category]||{})[q.historyStage]||q.historyStage)+'». В таблице показана их текущая стадия.</p>':'';
  if(kind==='leads'&&q.transitionFrom&&q.transitionTo){var stageNames=(live.stageNames||{})[q.category]||{};historyNote='<p class="funnel-history-note">Сделки, достигшие этапа «'+esc(stageNames[q.transitionTo]||q.transitionTo)+'» с учётом пропущенных основных этапов. В таблице показана текущая стадия.</p>'}
  if(kind==='leads'&&q.cumulativeStage)historyNote='<p class="funnel-history-note">Сделки, достигшие выбранного основного этапа с учётом пропущенных этапов. В таблице показана текущая стадия.</p>';
  if(kind==='leads'&&q.sideStage)historyNote='<p class="funnel-history-note">Сделки, сейчас находящиеся на отложенном этапе.</p>';
  var fl=L.filters.map(function(f){return '<select class="inp" data-filter="'+f.k+'"><option value="">'+f.lb+'</option>'+f.opts.map(function(o){return '<option value="'+esc(o[0])+'"'+(q[f.k]===o[0]?' selected':'')+'>'+esc(o[1])+'</option>'}).join('')+'</select>'}).join('');
  var values=kind==='tasks'?[['Всего',fmt(taskCache.summary.total)],['Просрочено',fmt(taskCache.summary.overdue)],['Выполнено',fmt(taskCache.summary.done)]]:(L.sum?L.sum(rows):[]);
  var sum=values.length?'<div class="sum-row">'+values.map(function(s){return '<div class="card sum"><small>'+s[0]+'</small><b>'+s[1]+'</b></div>'}).join('')+'</div>':'';
  var shown=kind==='tasks'?rows:rows.slice(0,listState.limit);
  app.innerHTML=activityReportHtml+'<div class="crumbs"><a data-go="#/">Панель управления</a> › <span>'+L.crumb+'</span></div>'+
   '<div class="page-h"><h1>'+L.title+' <span class="cnt">'+fmt(kind==='tasks'?taskCache.summary.total:rows.length)+'</span></h1><span class="badge b-gray">'+(L.noPeriod?'Текущее состояние на '+dRu(TODAY):P().label)+'</span><div class="spacer" style="flex:1"></div></div>'+historyNote+sum+
   '<div class="card"><div class="filters"><label class="inp search">'+ico('i-search')+'<input id="lsearch" placeholder="Поиск…" value="'+esc(q.q||'')+'"></label>'+fl+(Object.keys(q).length?'<button class="btn btn-o" data-go="#/'+kind+'">Сбросить</button>':'')+'</div>'+
   '<div class="tw"><table><thead><tr>'+L.head.map(function(h){return '<th>'+h+'</th>'}).join('')+'</tr></thead><tbody>'+(shown.length?shown.map(L.row).join(''):'<tr><td colspan="'+L.head.length+'" class="empty">Ничего не найдено</td></tr>')+'</tbody></table></div>'+
   (kind==='tasks'?(taskCache.hasMore?'<div class="more"><button class="btn btn-g" data-task-more>Показать ещё</button></div>':''):rows.length>shown.length?'<div class="more"><button class="btn btn-g" data-more>Показать ещё ('+(rows.length-shown.length)+')</button></div>':'')+'</div>';
  var inp=document.getElementById('lsearch'),tm;
  inp.addEventListener('input',function(){clearTimeout(tm);tm=setTimeout(function(){setQ('q',inp.value)},300)});
  if(q.q){inp.focus();inp.setSelectionRange(inp.value.length,inp.value.length)}
}
function setQ(k,v){var r=parseHash();if(v)r.q[k]=v;else delete r.q[k];listState.limit=50;
  var qs=Object.keys(r.q).map(function(x){return x+'='+encodeURIComponent(r.q[x])}).join('&');go('#/'+r.path.join('/')+(qs?'?'+qs:''))}

function renderEmployee(id){
  var e=EM[id];if(!e)return renderDash();
  if(!ensureTaskPage({who:id}))return;
  var ms=P().range,tasks=taskCache.rows,totals=taskStats(id);
  var od=totals.overdue,done=totals.done;
  var leads=DB.leads.filter(function(l){return l.mgr===id&&inRange(ms,l.date)}),sold=leads.filter(function(l){return l.won});
  var svc=DB.service.filter(function(s){return s.mgr===id&&inRange(ms,s.date)});
  var stats=[['Всего задач',totals.total],['Просрочено','<span class="red">'+od+'</span>'],['Выполнено',done]];
  if(e.sales)stats.push(['Сделки за период',leads.length],['Успешные',sold.length],['Сумма успешных сделок',mln(sold.reduce(function(a,l){return a+l.amount},0))+' млн ₸']);
  if(e.svc)stats.push(['Обращения',svc.length],['Записей на сервис',svc.filter(function(s){return s.status==='done'}).length]);
  app.innerHTML='<div class="crumbs"><a data-go="#/">Панель управления</a> › <a data-go="#/staff">Эффективность</a> › <span>'+esc(e.name)+'</span></div>'+
   '<div class="card emp-head">'+ava(e,'lg')+'<div style="flex:1;min-width:180px"><h1>'+esc(e.full)+'</h1><p>'+esc(e.role)+'</p></div>'+(e.sales?'<button class="btn btn-g" data-go="#/sales?mgr='+id+'">Продажи сотрудника</button>':'')+(e.svc?'<button class="btn btn-g" data-go="#/service?mgr='+id+'">Обращения</button>':'')+'</div>'+
   '<div class="sum-row">'+stats.map(function(s){return '<div class="card sum"><small>'+s[0]+'</small><b>'+s[1]+'</b></div>'}).join('')+'</div>'+
   '<div class="card tasks-card"><div class="ph">'+ico('i-clock')+'<h2>Задачи сотрудника</h2></div><div class="tw"><table><thead><tr><th></th><th>Задача</th><th>Исполнитель</th><th>Срок</th><th>Статус</th></tr></thead><tbody>'+tasks.map(taskRow).join('')+'</tbody></table></div>'+(taskCache.hasMore?'<div class="more"><button class="btn btn-g" data-task-more>Показать ещё</button></div>':'')+'</div>';
}

function renderStaff(){
  var ms=P().range;
  var rows=EMP.map(function(e){var t=taskStats(e.id),l=DB.leads.filter(function(x){return x.mgr===e.id&&inRange(ms,x.date)}),s=l.filter(function(x){return x.won});
    return {e:e,tasks:t.total,od:t.overdue,done:t.done,leads:l.length,deals:l.filter(function(x){return x.open}).length,sales:s.length,rev:s.reduce(function(a,x){return a+x.amount},0)}});
  app.innerHTML='<div class="crumbs"><a data-go="#/">Панель управления</a> › <span>Эффективность</span></div><div class="page-h"><h1>Эффективность сотрудников</h1><span class="badge b-gray">'+P().label+'</span></div>'+
   '<div class="card"><div class="tw"><table><thead><tr><th>Сотрудник</th><th>Должность</th><th class="num">Сделки</th><th class="num">В работе</th><th class="num">Успешные</th><th class="r">Сумма успешных сделок</th><th class="num">Задачи</th><th class="num">Выполнено</th><th class="num">Просрочено</th></tr></thead><tbody>'+
   rows.map(function(r){var sl=r.e.sales;return '<tr class="cl" data-go="#/employee/'+r.e.id+'"><td>'+personCell(r.e.id)+'</td><td>'+esc(r.e.role)+'</td><td class="num">'+(sl?r.leads:'—')+'</td><td class="num">'+(sl?r.deals:'—')+'</td><td class="num">'+(sl?r.sales:'—')+'</td><td class="r" style="white-space:nowrap">'+(sl?mln(r.rev)+' млн ₸':'—')+'</td><td class="num">'+r.tasks+'</td><td class="num">'+r.done+'</td><td class="num '+(r.od?'red':'')+'">'+r.od+'</td></tr>'}).join('')+'</tbody></table></div></div>';
}

/* ================= MODALS ================= */
var ov=document.getElementById('ov'),modal=document.getElementById('modal');
function openModal(title,body,foot,onMount){modal.innerHTML='<div class="mh"><h3>'+title+'</h3><button class="x" data-close>'+ico('i-x')+'</button></div><div class="mb">'+body+'</div><div class="mf">'+foot+'</div>';ov.classList.add('open');if(onMount)onMount();var f=modal.querySelector('input,select');if(f&&window.innerWidth>640)f.focus()}
function closeModal(){ov.classList.remove('open')}
ov.addEventListener('mousedown',function(e){if(e.target===ov)closeModal()});
document.addEventListener('keydown',function(e){if(e.key==='Escape'){closeModal();closeMenus()}});
function opts(arr,sel){return arr.map(function(o){return '<option value="'+o[0]+'"'+(o[0]===sel?' selected':'')+'>'+esc(o[1])+'</option>'}).join('')}
function val(id){return document.getElementById(id).value.trim()}
var toastT;function toast(t){var el=document.getElementById('toast');el.textContent=t;el.classList.add('show');clearTimeout(toastT);toastT=setTimeout(function(){el.classList.remove('show')},2600)}
function leadCard(id){var d=DB.leads.find(function(r){return r.id===id});if(!d)return;openModal('Сделка Bitrix','<div class="kv"><span>Название</span><b>'+esc(d.name)+'</b><span>Воронка</span><div>'+esc(d.phone)+'</div><span>Стадия</span><div>'+stageBadge(d.stage)+'</div><span>Сумма сделки</span><div>'+mln1(d.exp)+'</div><span>Ответственный</span><div>'+esc(EM[d.mgr].name)+'</div><span>Создана</span><div>'+dRu(d.date)+'</div></div>','<a class="btn btn-p" href="/crm/deal/details/'+Number(id.slice(1))+'/" target="_blank" rel="noopener">Открыть в Bitrix</a><button class="btn btn-o" data-close>Закрыть</button>')}
function svcCard(id){var d=DB.service.find(function(r){return r.id===id});if(!d)return;openModal('Заявка в сервис','<div class="kv"><span>Название</span><b>'+esc(d.client)+'</b><span>Статус</span><div>'+esc(SVC_ST[d.status][0])+'</div><span>Ответственный</span><div>'+esc(EM[d.mgr].name)+'</div><span>Создана</span><div>'+dRu(d.date)+'</div></div>','<a class="btn btn-p" href="/crm/deal/details/'+Number(id.slice(1))+'/" target="_blank" rel="noopener">Открыть в Bitrix</a><button class="btn btn-o" data-close>Закрыть</button>')}

/* ================= MENUS & EVENTS ================= */
function closeMenus(){}

document.addEventListener('click',function(e){
  var t=e.target.closest('[data-lead],[data-svc],[data-more],[data-task-more],[data-funnel-expand],[data-funnel-pick],[data-activity-refresh],[data-close],[data-go]');
  if(!t){closeMenus();return}
  closeMenus();
  if(t.hasAttribute('data-lead')){leadCard(t.getAttribute('data-lead'));return}
  if(t.hasAttribute('data-svc')){svcCard(t.getAttribute('data-svc'));return}
  if(t.hasAttribute('data-task-more')){var route=parseHash();requestTaskPage(route.path[0]==='employee'?{who:route.path[1]}:route.q,true);return}
  if(t.hasAttribute('data-funnel-expand')){funnelExpanded=!funnelExpanded;render();return}
  if(t.hasAttribute('data-funnel-pick')){funnelChoice=t.getAttribute('data-funnel-pick');funnelExpanded=false;render();return}
  if(t.hasAttribute('data-activity-refresh')){activityCache.key=null;render();return}
  if(t.hasAttribute('data-more')){listState.limit+=100;render();return}
  if(t.hasAttribute('data-close')){closeModal();return}
  if(t.hasAttribute('data-go')){closeModal();listState.limit=50;go(t.getAttribute('data-go'))}
});
document.addEventListener('change',function(e){if(e.target.hasAttribute('data-activity-who')){activityWho=e.target.value;render();return}if(e.target.hasAttribute('data-lead-reason')){leadReason=e.target.value;render();return}if(e.target.hasAttribute('data-lead-outcome')){leadOutcome=e.target.value;render();return}if(e.target.hasAttribute('data-funnel-select')){funnelChoice=e.target.value;funnelExpanded=false;render();return}var f=e.target.getAttribute&&e.target.getAttribute('data-filter');if(f)setQ(f,e.target.value)});


function applyPipelines(ids){selectedPipelines=ids.slice();funnelChoice=ids.filter(function(id){return id!==7}).length===1?String(ids.filter(function(id){return id!==7})[0]):'selected';funnelExpanded=false;DB.leads=allDeals.filter(function(r){return ids.indexOf(r.category)>=0});DB.service=allService.filter(function(r){return ids.indexOf(r.category)>=0});rebuildStages();LISTS.leads.filters[1].opts=STAGES.map(function(s,i){return [String(i),s]});document.getElementById('pipelineLbl').textContent=ids.length===PIPELINES.length?'Все воронки':'Воронки: '+ids.length;listState.limit=50;var route=parseHash();delete route.q.stage;delete route.q.historyStage;delete route.q.transitionFrom;delete route.q.transitionTo;delete route.q.cumulativeStage;delete route.q.sideStage;var qs=Object.keys(route.q).map(function(k){return k+'='+encodeURIComponent(route.q[k])}).join('&');go('#/'+route.path.join('/')+(qs?'?'+qs:''));}
function openPipelines(){closeMenus();openModal('Воронки','<p style="color:var(--muted)">Задачи показываются по вашим правам Bitrix и не зависят от воронок.</p>'+PIPELINES.map(function(p){return '<label style="display:flex;gap:10px;align-items:center;margin:12px 0"><input type="checkbox" name="live-pipeline" value="'+p.id+'" '+(selectedPipelines.indexOf(p.id)>=0?'checked':'')+'>'+esc(p.name)+'</label>'}).join(''),'<button class="btn btn-g" id="pipelineAll">Все</button><button class="btn btn-p" id="pipelineApply">Применить</button>',function(){document.getElementById('pipelineAll').onclick=function(){document.querySelectorAll('[name="live-pipeline"]').forEach(function(el){el.checked=true})};document.getElementById('pipelineApply').onclick=function(){var ids=Array.from(document.querySelectorAll('[name="live-pipeline"]:checked')).map(function(el){return Number(el.value)});applyPipelines(ids);closeModal();toast('Фильтр воронок применён')};})}
document.getElementById('pipelineBtn').addEventListener('click',openPipelines);

render();
})();
</script>
</body>
</html>
