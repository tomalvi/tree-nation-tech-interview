<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const customers = ref([])
const hourlyVisits = ref([])
const loading = ref(true)

const fetchData = async (showLoading = true) => {
  if (showLoading) {
    loading.value = true
  }
  const [c, h] = await Promise.all([
    axios.get('/api/customers'),
    axios.get('/api/visits/hourly')
  ])
  customers.value = c.data
  hourlyVisits.value = h.data
  if (showLoading) {
    loading.value = false
  }


}

const totalVisits = computed(() => customers.value.reduce((acc, c) => acc + (c.visits_count || 0), 0))
const totalTrees = computed(() => customers.value.reduce((acc, c) => acc + c.trees_planted, 0))
const totalCustomers = computed(() => customers.value.length)


const perfectHourlyVisits = computed(() => {
  const startHour = 8
  const endHour = 20
  const filledData = []

  for (let h = startHour; h <= endHour; h++) {
    const realData = hourlyVisits.value.find(item => item.hour === h)
    
    filledData.push({
      hour: h,
      total: realData ? realData.total : 0 
    })
  }

  return filledData
})


const maxHourly = computed(() => {
  if (perfectHourlyVisits.value.length === 0) return 1
  const max = Math.max(...perfectHourlyVisits.value.map(h => h.total))
  return max > 0 ? max : 1
})


const barHeight = (total) => Math.max((total / maxHourly.value) * 100, 4)
const formatHour = (h) => `${parseInt(h).toString().padStart(2, '0')}:00`

console.log(barHeight);

const doorOpen = ref(false)
const doorLoading = ref(false)
const lastVisitor = ref(null)
const treePlanted = ref(false)

const simulateVisit = async () => {
  if (doorLoading.value || customers.value.length === 0) return
  doorLoading.value = true
  doorOpen.value = true
  treePlanted.value = false

  const randomIndex = Math.floor(Math.random() * customers.value.length)
  const customer = customers.value[randomIndex]
  const prevTrees = customer.trees_planted

  try {
    const res = await axios.post('/api/visits', { customer_id: customer.id })
    lastVisitor.value = { name: customer.name, ...res.data }
    if (res.data.trees_planted > prevTrees) treePlanted.value = true
    await fetchData(false)
  } catch (e) {
    console.error(e)
  }

  setTimeout(() => {
    doorOpen.value = false
    doorLoading.value = false
  }, 1800)
}

onMounted(fetchData)
</script>

<template>
  <div class="app">
    <div class="bg-texture"></div>

    <header class="header">
      <div class="header-inner">
        <div class="brand">
          <div class="brand-icon">
            <img :src="`/favicon.png`" alt="Tree Nation Icon">
          </div>
          <div>
            <div class="brand-name">Tree Nation</div>
            <div class="brand-sub">Visit Tracker by Tomas Almonte</div>
          </div>
        </div>
        <!-- <button class="refresh-btn" @click="fetchData">↻ Refresh</button> -->
      </div>
    </header>

    <main class="main">
      <div v-if="loading" class="loading">
        <div class="leaf-spinner">🌿</div>
        <p>Loading data...</p>
      </div>

      <template v-else>

        <section class="stats">
          <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-value">{{ totalCustomers }}</div>
            <div class="stat-label">Customers</div>
          </div>
          <div class="stat-card accent">
            <div class="stat-icon">🚶</div>
            <div class="stat-value">{{ totalVisits }}</div>
            <div class="stat-label">Total Visits</div>
          </div>
          <div class="stat-card green">
            <div class="stat-icon">🌳</div>
            <div class="stat-value">{{ totalTrees }}</div>
            <div class="stat-label">Trees Planted</div>
          </div>
        </section>

        <section class="section">
          <div class="section-header">
            <h2>Shop Entrance Simulator</h2>
            <span class="section-badge">Device simulation</span>
          </div>
          <div class="door-card">
            <div class="door-scene">
              <div class="door-frame">
                <div class="door-left" :class="{ open: doorOpen }"></div>
                <div class="door-right" :class="{ open: doorOpen }"></div>
                <div class="door-top"></div>
                <div class="door-center-line"></div>
                <div class="person" :class="{ walking: doorOpen }">🚶</div>
              </div>
            </div>

            <div class="door-info">
              <div class="door-title">Automatic Glass Door</div>
              <div class="door-sub">Click to simulate a random customer entering the shop</div>

              <button class="door-btn" @click="simulateVisit" :disabled="doorLoading">
                {{ doorLoading ? 'Customer entering...' : '🚪 Simulate Visit' }}
              </button>

              <transition name="fade">
                <div v-if="lastVisitor" class="visitor-toast" :class="{ tree: treePlanted }">
                  <div class="visitor-name">{{ lastVisitor.name }} just entered</div>
                  <div class="visitor-stats">
                    {{ lastVisitor.total_visits }} visits · 🌳 {{ lastVisitor.trees_planted }} trees
                  </div>
                  <div v-if="treePlanted" class="tree-alert">🎉 A new tree was planted!</div>
                </div>
              </transition>
            </div>
          </div>
        </section>

        <section class="section">
          <div class="section-header">
            <h2>Visits per Hour</h2>
            <span class="section-badge">Today</span>
          </div>
          <div class="chart-card">
            <div v-if="hourlyVisits.length === 0" class="empty">No visit data yet.</div>
            <div v-else class="chart">
              <div v-for="item in perfectHourlyVisits" :key="item.hour" class="bar-wrap">
                <div class="bar-label-top">{{ item.total }}</div>
                <div class="bar-outer">
                  <div class="tree-bar bar-inner" :style="{ height: barHeight(item.total) + '%' }"></div>
                </div>
                <div class="bar-label-bottom">{{ formatHour(item.hour) }}</div>
              </div>
            </div>
          </div>
        </section>

        <section class="section">
          <div class="section-header">
            <h2>Customers</h2>
            <span class="section-badge">{{ customers.length }} total</span>
          </div>
          <div class="table-card">
            <div v-if="customers.length === 0" class="empty">No customers yet.</div>
            <table v-else class="table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Total Visits</th>
                  <th>Trees Planted</th>
                  <th>Last Visit</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="customer in customers" :key="customer.id">
                  <td class="name">
                    <div class="avatar">{{ customer.name.charAt(0).toUpperCase() }}</div>
                    {{ customer.name }}
                  </td>
                  <td class="email">{{ customer.email }}</td>
                  <td class="visits">{{ customer.visits_count }}</td>
                  <td class="trees">
                    <span class="tree-badge">🌳 {{ customer.trees_planted }}</span>
                  </td>
                  <td class="last">
                    {{ customer.last_visit_at ? new Date(customer.last_visit_at).toLocaleString() : '—' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

      </template>
    </main>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cabinet+Grotesk:wght@400;500;700;800&family=Instrument+Serif:ital@0;1&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.app {
  min-height: 100vh;
  background: #f5f0e8;
  color: #1a1a12;
  font-family: 'Cabinet Grotesk', sans-serif;
  position: relative;
}

.bg-texture {
  position: fixed; inset: 0;
  background-image: radial-gradient(circle at 20% 20%, rgba(74,124,60,0.06) 0%, transparent 50%),
    radial-gradient(circle at 80% 80%, rgba(74,124,60,0.04) 0%, transparent 50%);
  pointer-events: none; z-index: 0;
}

.header {
  position: sticky; top: 0; z-index: 100;
  background: rgba(245,240,232,0.92);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(74,124,60,0.15);
  padding: 0 2rem;
}
.header-inner {
  max-width: 1100px; margin: 0 auto;
  display: flex; justify-content: space-between; align-items: center;
  height: 68px;
}
.brand { display: flex; align-items: center; gap: 12px; }
.brand-icon {
  display: inline-block; 
  width: 40px;  
  height: 40px; 
}

.brand-icon img {
  width: 100%;
  height: 100%;
  object-fit: contain; 
}
.brand-name { font-family: 'Instrument Serif', serif; font-size: 1.4rem; line-height: 1; }
.brand-sub { font-size: 0.72rem; color: #888; text-transform: uppercase; letter-spacing: 0.1em; }


.main {
  max-width: 1100px; margin: 0 auto;
  padding: 2.5rem 2rem; position: relative; z-index: 1;
}

.loading {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; gap: 1rem; padding: 6rem; color: #888;
}
.leaf-spinner { font-size: 2.5rem; animation: spin 1.5s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.stats {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 1.25rem; margin-bottom: 2.5rem;
}
.stat-card {
  background: white; border: 1px solid rgba(0,0,0,0.07);
  border-radius: 16px; padding: 1.5rem 1.75rem;
  display: flex; flex-direction: column; gap: 0.25rem;
  transition: transform 0.2s;
}
.stat-card:hover { transform: translateY(-2px); }
.stat-card.accent { background: #1a1a12; color: #f5f0e8; }
.stat-card.green { background: #2d5a27; color: #f5f0e8; }
.stat-icon { font-size: 1.5rem; margin-bottom: 0.5rem; }
.stat-value { font-size: 2.5rem; font-weight: 800; line-height: 1; }
.stat-label { font-size: 0.8rem; opacity: 0.6; text-transform: uppercase; letter-spacing: 0.08em; }

.section { margin-bottom: 2.5rem; }
.section-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
.section-header h2 { font-family: 'Instrument Serif', serif; font-size: 1.4rem; font-weight: 400; }
.section-badge {
  background: rgba(74,124,60,0.12); color: #2d5a27;
  padding: 0.2rem 0.7rem; border-radius: 100px;
  font-size: 0.75rem; font-weight: 600;
}

.door-card {
  background: white; border: 1px solid rgba(0,0,0,0.07);
  border-radius: 16px; padding: 2rem;
  display: flex; gap: 3rem; align-items: center;
}
.door-scene {
  flex-shrink: 0; display: flex;
  align-items: center; justify-content: center;
}
.door-frame {
  position: relative;
  width: 140px; height: 180px;
  border: 3px solid #8b7355;
  border-radius: 4px 4px 0 0;
  background: #e8e0d0;
  overflow: hidden;
}
.door-top {
  position: absolute; top: 0; left: 0; right: 0;
  height: 8px; background: #8b7355;
}
.door-center-line {
  position: absolute; top: 0; bottom: 0; left: 50%;
  width: 2px; background: rgba(139,115,85,0.3);
  transform: translateX(-50%);
  z-index: 3;
}
.door-left, .door-right {
  position: absolute; top: 0; bottom: 0;
  width: 50%;
  background: linear-gradient(135deg, rgba(200,230,255,0.7) 0%, rgba(150,200,255,0.4) 50%, rgba(200,230,255,0.6) 100%);
  border: 1px solid rgba(255,255,255,0.8);
  transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 2;
}

.door-left { left: 0; transform-origin: center left; }
.door-right { right: 0; transform-origin: right center; }
.door-left.open { transform: perspective(400px) rotateY(-75deg); }
.door-right.open { transform: perspective(400px) rotateY(75deg); }

.door-left::after, .door-right::after {
  content: '';
  position: absolute; top: 50%;
  width: 4px; height: 20px;
  background: rgba(139,115,85,0.6);
  border-radius: 2px;
  transform: translateY(-50%);
}
.door-left::after { right: 6px; }
.door-right::after { left: 6px; }

.person {
  position: absolute; bottom: 10px; left: 50%;
  transform: translateX(-50%) translateY(60px);
  font-size: 1.8rem; z-index: 4;
  transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.4s;
}
.person.walking { transform: translateX(-50%) translateY(0px); }

.door-info { flex: 1; }
.door-title { font-family: 'Instrument Serif', serif; font-size: 1.2rem; margin-bottom: 0.35rem; }
.door-sub { font-size: 0.85rem; color: #888; margin-bottom: 1.5rem; }

.door-btn {
  font-family: 'Cabinet Grotesk', sans-serif;
  background: #2d5a27; color: #f5f0e8;
  border: none; padding: 0.75rem 1.75rem;
  border-radius: 100px; font-size: 0.95rem; font-weight: 700;
  cursor: pointer; transition: all 0.2s;
  margin-bottom: 1.25rem;
}
.door-btn:hover:not(:disabled) { background: #3d7a35; transform: scale(1.03); }
.door-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.visitor-toast {
  background: #f0f7ee;
  border: 1px solid rgba(74,124,60,0.2);
  border-radius: 12px; padding: 1rem 1.25rem;
  border-left: 3px solid #2d5a27;
}
.visitor-toast.tree { background: #fff9e6; border-color: rgba(255,180,0,0.3); border-left-color: #f0b000; }
.visitor-name { font-weight: 700; font-size: 0.95rem; margin-bottom: 0.25rem; }
.visitor-stats { font-size: 0.82rem; color: #666; }
.tree-alert { margin-top: 0.5rem; font-weight: 700; color: #b07000; font-size: 0.9rem; }

.chart-card {
  background: white; border: 1px solid rgba(0,0,0,0.07);
  border-radius: 16px; padding: 1.75rem;
}
.chart {
  display: flex;
  justify-content: space-between;
  align-items: stretch;          
height: 220px;                 
  width: 100%;                   
}
.bar-wrap { display: flex; flex-direction: column; align-items: center; gap: 4px; flex: 1; min-width: 40px; }
.bar-label-top { font-size: 0.72rem; font-weight: 700; color: #2d5a27; }
.bar-outer { 
  width: 100%; 
  flex: 1; 
  display: flex; 
  align-items: flex-end; 
  justify-content: center; 
}

.bar-inner.tree-bar {
  width: 32px;
  
  background-image: url("/arbol.svg");
  background-repeat: repeat-y;
  background-position: bottom center;
  background-size: 32px 32px; 
  
  border-radius: 0;
  transition: height 0.5s ease; 
  min-height: 0;
}



.bar-label-bottom { font-size: 0.65rem; color: #999; white-space: nowrap; }

.table-card { background: white; border: 1px solid rgba(0,0,0,0.07); border-radius: 16px; overflow: hidden; }
.table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.table thead tr { border-bottom: 1px solid rgba(0,0,0,0.07); }
.table th {
  text-align: left; padding: 0.9rem 1.25rem;
  font-size: 0.72rem; text-transform: uppercase;
  letter-spacing: 0.08em; color: #aaa; font-weight: 600;
}
.table tbody tr { border-bottom: 1px solid rgba(0,0,0,0.04); transition: background 0.15s; }
.table tbody tr:last-child { border-bottom: none; }
.table tbody tr:hover { background: #faf8f4; }
.table td { padding: 0.9rem 1.25rem; vertical-align: middle; }
.id { color: #ccc; font-size: 0.8rem; }
.name { display: flex; align-items: center; gap: 0.75rem; font-weight: 600; }


.avatar {
  width: 34px; height: 34px; border-radius: 50%;
  background: #f0ebe0; border: 1px solid rgba(0,0,0,0.08);
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 0.85rem; color: #2d5a27; flex-shrink: 0;
}

.visits { color: #555; }
.tree-badge {
  background: rgba(74,124,60,0.1); color: #2d5a27;
  padding: 0.25rem 0.75rem; border-radius: 100px;
  font-size: 0.85rem; font-weight: 600;
}
.last { color: #aaa; font-size: 0.82rem; }
.empty { padding: 3rem; text-align: center; color: #bbb; font-size: 0.9rem; }

.fade-enter-active, .fade-leave-active { transition: all 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(8px); }
</style>