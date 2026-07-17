<?php
require_once 'config.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IoT Relay Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;padding:20px}
        .relay{display:inline-block;margin:8px;padding:12px;border:1px solid #ddd;border-radius:6px}
        .on{background:#c8f7c5}
        .off{background:#f7c7c7}
        button{padding:8px 12px;margin-top:8px}
    </style>
</head>
<body>
    <h2>Relay Controls (5 channels)</h2>
    <div id="relays"></div>

    <h3>Switch Events (latest)</h3>
    <ul id="switches"></ul>

    <h3>LDR (Light) Graph</h3>
    <canvas id="ldrChart" width="800" height="250"></canvas>

    <script>
    async function fetchStates(){
        const r = await fetch('api/control.php');
        const j = await r.json();
        return j.states || {};
    }

    function renderRelays(states){
        const el = document.getElementById('relays');
        el.innerHTML = '';
        for(let i=1;i<=5;i++){
            const s = states[i] ? 1 : 0;
            const div = document.createElement('div');
            div.className = 'relay ' + (s? 'on':'off');
            div.innerHTML = '<strong>Relay '+i+'</strong><br><span>Status: '+(s? 'ON':'OFF')+'</span><br>';
            const btn = document.createElement('button');
            btn.textContent = s? 'Turn OFF' : 'Turn ON';
            btn.onclick = async ()=>{
                btn.disabled = true;
                await fetch('api/control.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'relay='+i+'&state='+(s?0:1)});
                await refreshAll();
                btn.disabled = false;
            };
            div.appendChild(btn);
            el.appendChild(div);
        }
    }

    async function fetchSwitchEvents(){
        const r = await fetch('switch_events.json.php');
        if (!r.ok) return [];
        const j = await r.json();
        return j.data || [];
    }

    async function renderSwitchList(){
        const ul = document.getElementById('switches');
        ul.innerHTML = '';
        const res = await fetch('api/receive.php?type=list_switches');
        const j = await res.json();
        if(!j.ok) return;
        for(const ev of j.data){
            const li = document.createElement('li');
            li.textContent = ev.event_time + ' — Switch ' + ev.switch_id + ' => ' + (ev.state? 'ON':'OFF');
            ul.appendChild(li);
        }
    }

    let chart;
    async function loadChart(){
        const res = await fetch('api/ldr_data.php?limit=200');
        const j = await res.json();
        const labels = j.data.map(x=>x.t);
        const values = j.data.map(x=>x.v);
        const ctx = document.getElementById('ldrChart').getContext('2d');
        if(chart) chart.destroy();
        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{label:'LDR',data:values,borderColor:'blue',fill:false}]
            },
            options: {scales:{x:{display:true,ticks:{maxRotation:45}}}}
        });
    }

    async function refreshAll(){
        const states = await fetchStates();
        renderRelays(states);
        await renderSwitchList();
        await loadChart();
    }

    refreshAll();
    setInterval(refreshAll, 5000);
    </script>
</body>
</html>
