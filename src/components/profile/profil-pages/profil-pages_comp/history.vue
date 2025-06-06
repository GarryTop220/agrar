<template>
  <div class="history-container">
    <div class="history-header">
      <h2 class="history-title">
        <img :src="Xz" alt="" class="xz"> Історія замовлень <img :src="Xz_2" alt="" class="xz">
      </h2>
    </div>
    
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Завантаження історії...</p>
    </div>
    
    <div v-else-if="orders.length === 0" class="no-history">
      <div class="no-history-icon">📋</div>
      <h3>Історія замовлень порожня</h3>
      <p>Тут будуть відображатися всі ваші завершені замовлення</p>
    </div>
    
    <div v-else class="history-list">
      <div class="filters">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Пошук по номеру замовлення або товару..."
          class="search-input"
        >
        <select v-model="sortOrder" class="sort-select">
          <option value="desc">Спочатку нові</option>
          <option value="asc">Спочатку старі</option>
        </select>
      </div>
      
      <div v-for="order in filteredOrders" :key="order.id" class="history-card">
        <div class="history-header-card">
          <div class="order-info">
            <h3 class="order-number">Замовлення №{{ order.id }}</h3>
            <p class="order-date">{{ formatDate(order.created_at) }}</p>
          </div>
          <div class="order-actions">
            <span class="status-badge completed">Завершено</span>
            <button @click="toggleOrderDetails(order.id)" class="details-btn">
              {{ expandedOrders.includes(order.id) ? 'Згорнути' : 'Деталі' }}
            </button>
          </div>
        </div>
        
        <div class="order-summary">
          <div class="summary-info">
            <span class="items-count">{{ order.items.length }} товар(ів)</span>
            <span class="order-total">{{ order.total_amount }} грн</span>
          </div>
        </div>
        
        <div v-if="expandedOrders.includes(order.id)" class="order-details-expanded">
          <div class="delivery-info">
            <h4>Інформація про доставку:</h4>
            <div class="info-grid">
              <div class="info-item">
                <strong>Отримувач:</strong> {{ order.name }} {{ order.surname }}
              </div>
              <div class="info-item">
                <strong>Телефон:</strong> {{ order.phone }}
              </div>
              <div class="info-item">
                <strong>Email:</strong> {{ order.email }}
              </div>
              <div class="info-item">
                <strong>Адреса:</strong> {{ order.address }}
              </div>
            </div>
          </div>
          
          <div class="order-items">
            <h4>Товари в замовленні:</h4>
            <div class="items-grid">
              <div v-for="item in order.items" :key="item.good_id" class="history-item">
                <img :src="item.image" :alt="item.title" class="item-image">
                <div class="item-details">
                  <h5>{{ item.title }}</h5>
                  <p class="item-price">{{ item.quantity }} шт. × {{ item.price }} грн</p>
                  <p class="item-subtotal">Сума: {{ (item.quantity * item.price).toFixed(2) }} грн</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Xz from "@/assets/xz.svg";
import Xz_2 from "@/assets/xz_2.svg";

export default {
  data() {
    return {
      Xz,
      Xz_2,
      orders: [],
      loading: true,
      expandedOrders: [],
      searchQuery: '',
      sortOrder: 'desc'
    };
  },
  computed: {
    filteredOrders() {
      let filtered = this.orders;
      
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(order => 
          order.id.toString().includes(query) ||
          order.items.some(item => item.title.toLowerCase().includes(query))
        );
      }
      
      return filtered.sort((a, b) => {
        const dateA = new Date(a.created_at);
        const dateB = new Date(b.created_at);
        return this.sortOrder === 'desc' ? dateB - dateA : dateA - dateB;
      });
    }
  },
  mounted() {
    this.fetchOrderHistory();
  },
  methods: {
    async fetchOrderHistory() {
      try {
        const userId = localStorage.getItem('user_id');
        if (!userId) {
          console.error('User ID не знайдено');
          return;
        }

        const response = await axios.get(`http://localhost/agrar_shop/Backend/get_user_orders.php?user_id=${userId}`);
        
        if (response.data.success) {
          this.orders = response.data.orders;
        } else {
          console.error('Помилка отримання історії:', response.data.message);
        }
      } catch (error) {
        console.error('Помилка запиту:', error);
      } finally {
        this.loading = false;
      }
    },
    toggleOrderDetails(orderId) {
      const index = this.expandedOrders.indexOf(orderId);
      if (index > -1) {
        this.expandedOrders.splice(index, 1);
      } else {
        this.expandedOrders.push(orderId);
      }
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('uk-UA', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }
  }
};
</script>

<style scoped>
.history-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
}

.history-title {
  font-weight: 700;
  font-size: 32px;
  color: #000;
  font-family: var(--font-family);
  text-align: center;
  margin-bottom: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.xz {
  width: 25px;
  height: 20px;
  margin: 0 10px;
}

.loading {
  text-align: center;
  padding: 40px;
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #84C551;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.no-history {
  text-align: center;
  padding: 60px 20px;
  background: #f8f9fa;
  border-radius: 10px;
}

.no-history-icon {
  font-size: 60px;
  margin-bottom: 20px;
}

.filters {
  display: flex;
  gap: 15px;
  margin-bottom: 25px;
  flex-wrap: wrap;
}

.search-input {
  flex: 1;
  min-width: 250px;
  padding: 10px 15px;
  border: 1px solid #ddd;
  border-radius: 25px;
  font-size: 14px;
}

.search-input:focus {
  outline: none;
  border-color: #84C551;
}

.sort-select {
  padding: 10px 15px;
  border: 1px solid #ddd;
  border-radius: 25px;
  background: white;
  font-size: 14px;
  cursor: pointer;
}

.history-card {
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  margin-bottom: 20px;
  overflow: hidden;
  transition: transform 0.2s;
}

.history-card:hover {
  transform: translateY(-2px);
}

.history-header-card {
  background: #f8f9fa;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #eee;
}

.order-number {
  margin: 0;
  color: #333;
  font-size: 18px;
}

.order-date {
  margin: 5px 0 0 0;
  color: #666;
  font-size: 14px;
}

.order-actions {
  display: flex;
  align-items: center;
  gap: 15px;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
}

.status-badge.completed {
  background-color: #d4edda;
  color: #155724;
}

.details-btn {
  background: #84C551;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 20px;
  cursor: pointer;
  font-size: 12px;
  transition: background-color 0.3s;
}

.details-btn:hover {
  background: #73b141;
}

.order-summary {
  padding: 15px 20px;
}

.summary-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.items-count {
  color: #666;
  font-size: 14px;
}

.order-total {
  font-weight: 600;
  font-size: 18px;
  color: #84C551;
}

.order-details-expanded {
  padding: 20px;
  border-top: 1px solid #eee;
  background: #fafafa;
}

.delivery-info {
  margin-bottom: 25px;
}

.delivery-info h4 {
  margin-bottom: 15px;
  color: #333;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 10px;
}

.info-item {
  color: #666;
  font-size: 14px;
}

.order-items h4 {
  margin-bottom: 15px;
  color: #333;
}

.items-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 15px;
}

.history-item {
  display: flex;
  align-items: center;
  background: white;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.item-image {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 5px;
  margin-right: 15px;
}

.item-details h5 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 14px;
}

.item-price {
  margin: 0 0 3px 0;
  color: #666;
  font-size: 12px;
}

.item-subtotal {
  margin: 0;
  color: #84C551;
  font-weight: 600;
  font-size: 13px;
}

@media (max-width: 768px) {
  .filters {
    flex-direction: column;
  }
  
  .search-input {
    min-width: 100%;
  }
  
  .history-header-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .order-actions {
    width: 100%;
    justify-content: space-between;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
  
  .items-grid {
    grid-template-columns: 1fr;
  }
}
</style>