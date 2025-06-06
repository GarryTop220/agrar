<template>
  <section class="admin-orders-container">
    <Menu />
    <div class="admin-orders-content">
      <h2 class="admin-orders-title">
        <img :src="Xz" alt="" class="xz"> Управління замовленнями <img :src="Xz_2" alt="" class="xz">
      </h2>
      
      <div class="orders-stats">
        <div class="stat-card">
          <div class="stat-number">{{ orders.length }}</div>
          <div class="stat-label">Всього замовлень</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">{{ totalRevenue.toFixed(2) }} грн</div>
          <div class="stat-label">Загальний дохід</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">{{ averageOrderValue.toFixed(2) }} грн</div>
          <div class="stat-label">Середній чек</div>
        </div>
      </div>
      
      <div class="orders-filters">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Пошук по номеру замовлення, імені або email..."
          class="search-input"
        >
        <select v-model="sortOrder" class="sort-select">
          <option value="desc">Спочатку нові</option>
          <option value="asc">Спочатку старі</option>
        </select>
      </div>
      
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p>Завантаження замовлень...</p>
      </div>
      
      <div v-else-if="filteredOrders.length === 0" class="no-orders">
        <h3>Замовлення не знайдено</h3>
        <p>Спробуйте змінити параметри пошуку</p>
      </div>
      
      <div v-else class="orders-table-container">
        <table class="orders-table">
          <thead>
            <tr>
              <th>№ Замовлення</th>
              <th>Клієнт</th>
              <th>Email</th>
              <th>Телефон</th>
              <th>Товарів</th>
              <th>Сума</th>
              <th>Дата</th>
              <th>Дії</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in paginatedOrders" :key="order.id" class="order-row">
              <td class="order-id">#{{ order.id }}</td>
              <td class="customer-name">{{ order.name }} {{ order.surname }}</td>
              <td class="customer-email">{{ order.email }}</td>
              <td class="customer-phone">{{ order.phone }}</td>
              <td class="items-count">{{ order.items_count }}</td>
              <td class="order-amount">{{ order.total_amount }} грн</td>
              <td class="order-date">{{ formatDate(order.created_at) }}</td>
              <td class="order-actions">
                <button @click="viewOrderDetails(order.id)" class="view-btn">
                  Переглянути
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        
        <div class="pagination" v-if="totalPages > 1">
          <button 
            @click="currentPage = Math.max(1, currentPage - 1)"
            :disabled="currentPage === 1"
            class="pagination-btn"
          >
            ← Попередня
          </button>
          
          <span class="pagination-info">
            Сторінка {{ currentPage }} з {{ totalPages }}
          </span>
          
          <button 
            @click="currentPage = Math.min(totalPages, currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="pagination-btn"
          >
            Наступна →
          </button>
        </div>
      </div>
    </div>
    
    <!-- Order Details Modal -->
    <div v-if="selectedOrder" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Деталі замовлення #{{ selectedOrder.id }}</h3>
          <button @click="closeModal" class="close-btn">&times;</button>
        </div>
        
        <div class="modal-body">
          <div class="order-info-grid">
            <div class="info-section">
              <h4>Інформація про клієнта</h4>
              <p><strong>Ім'я:</strong> {{ selectedOrder.name }} {{ selectedOrder.surname }}</p>
              <p><strong>Email:</strong> {{ selectedOrder.email }}</p>
              <p><strong>Телефон:</strong> {{ selectedOrder.phone }}</p>
              <p><strong>Адреса:</strong> {{ selectedOrder.address }}</p>
            </div>
            
            <div class="info-section">
              <h4>Деталі замовлення</h4>
              <p><strong>Дата:</strong> {{ formatDate(selectedOrder.created_at) }}</p>
              <p><strong>Загальна сума:</strong> {{ selectedOrder.total_amount }} грн</p>
              <p><strong>Кількість товарів:</strong> {{ selectedOrder.items ? selectedOrder.items.length : 0 }}</p>
            </div>
          </div>
          
          <div class="order-items-section" v-if="selectedOrder.items">
            <h4>Товари в замовленні</h4>
            <div class="modal-items-list">
              <div v-for="item in selectedOrder.items" :key="item.good_id" class="modal-item">
                <img :src="item.image" :alt="item.title" class="modal-item-image">
                <div class="modal-item-details">
                  <h5>{{ item.title }}</h5>
                  <p>{{ item.quantity }} шт. × {{ item.price }} грн</p>
                  <p class="item-total">Сума: {{ (item.quantity * item.price).toFixed(2) }} грн</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <Footer />
  </section>
</template>

<script>
import Menu from '@/components/Menu.vue';
import Footer from '@/components/footer.vue';
import axios from 'axios';
import Xz from "@/assets/xz.svg";
import Xz_2 from "@/assets/xz_2.svg";

export default {
  components: {
    Menu,
    Footer
  },
  data() {
    return {
      Xz,
      Xz_2,
      orders: [],
      selectedOrder: null,
      loading: true,
      searchQuery: '',
      sortOrder: 'desc',
      currentPage: 1,
      itemsPerPage: 10
    };
  },
  computed: {
    filteredOrders() {
      let filtered = this.orders;
      
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(order => 
          order.id.toString().includes(query) ||
          order.name.toLowerCase().includes(query) ||
          order.surname.toLowerCase().includes(query) ||
          order.email.toLowerCase().includes(query) ||
          order.phone.includes(query)
        );
      }
      
      return filtered.sort((a, b) => {
        const dateA = new Date(a.created_at);
        const dateB = new Date(b.created_at);
        return this.sortOrder === 'desc' ? dateB - dateA : dateA - dateB;
      });
    },
    paginatedOrders() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredOrders.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredOrders.length / this.itemsPerPage);
    },
    totalRevenue() {
      return this.orders.reduce((sum, order) => sum + parseFloat(order.total_amount), 0);
    },
    averageOrderValue() {
      return this.orders.length > 0 ? this.totalRevenue / this.orders.length : 0;
    }
  },
  mounted() {
    this.fetchAllOrders();
  },
  methods: {
    async fetchAllOrders() {
      try {
        const response = await axios.get('http://localhost/agrar_shop/Backend/get_all_orders.php');
        
        if (response.data.success) {
          this.orders = response.data.orders;
        } else {
          console.error('Помилка отримання замовлень:', response.data.message);
        }
      } catch (error) {
        console.error('Помилка запиту:', error);
      } finally {
        this.loading = false;
      }
    },
    async viewOrderDetails(orderId) {
      try {
        const response = await axios.get(`http://localhost/agrar_shop/Backend/get_order_details.php?order_id=${orderId}`);
        
        if (response.data.success) {
          this.selectedOrder = response.data.order;
        } else {
          console.error('Помилка отримання деталей замовлення:', response.data.message);
        }
      } catch (error) {
        console.error('Помилка запиту:', error);
      }
    },
    closeModal() {
      this.selectedOrder = null;
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('uk-UA', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }
  }
};
</script>

<style scoped>
.admin-orders-container {
  min-height: 100vh;
  background-color: #f8f9fa;
}

.admin-orders-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  margin-top: -200px;
}

.admin-orders-title {
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

.orders-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  padding: 25px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.stat-number {
  font-size: 28px;
  font-weight: 700;
  color: #84C551;
  margin-bottom: 5px;
}

.stat-label {
  color: #666;
  font-size: 14px;
}

.orders-filters {
  display: flex;
  gap: 15px;
  margin-bottom: 25px;
  flex-wrap: wrap;
}

.search-input {
  flex: 1;
  min-width: 300px;
  padding: 12px 15px;
  border: 1px solid #ddd;
  border-radius: 25px;
  font-size: 14px;
}

.search-input:focus {
  outline: none;
  border-color: #84C551;
}

.sort-select {
  padding: 12px 15px;
  border: 1px solid #ddd;
  border-radius: 25px;
  background: white;
  font-size: 14px;
  cursor: pointer;
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

.no-orders {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 10px;
}

.orders-table-container {
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
}

.orders-table th {
  background: #f8f9fa;
  padding: 15px 10px;
  text-align: left;
  font-weight: 600;
  color: #333;
  border-bottom: 1px solid #eee;
}

.orders-table td {
  padding: 15px 10px;
  border-bottom: 1px solid #f0f0f0;
}

.order-row:hover {
  background-color: #f8f9fa;
}

.order-id {
  font-weight: 600;
  color: #84C551;
}

.customer-name {
  font-weight: 500;
}

.customer-email {
  color: #666;
  font-size: 14px;
}

.customer-phone {
  color: #666;
  font-size: 14px;
}

.items-count {
  text-align: center;
}

.order-amount {
  font-weight: 600;
  color: #333;
}

.order-date {
  color: #666;
  font-size: 14px;
}

.view-btn {
  background: #84C551;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 20px;
  cursor: pointer;
  font-size: 12px;
  transition: background-color 0.3s;
}

.view-btn:hover {
  background: #73b141;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  padding: 20px;
  background: #f8f9fa;
}

.pagination-btn {
  background: #84C551;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 25px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.pagination-btn:hover:not(:disabled) {
  background: #73b141;
}

.pagination-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.pagination-info {
  color: #666;
  font-weight: 500;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 10px;
  max-width: 800px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.modal-header h3 {
  margin: 0;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
}

.close-btn:hover {
  color: #333;
}

.modal-body {
  padding: 20px;
}

.order-info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.info-section h4 {
  margin-bottom: 15px;
  color: #333;
  border-bottom: 1px solid #eee;
  padding-bottom: 10px;
}

.info-section p {
  margin: 8px 0;
  color: #666;
}

.order-items-section h4 {
  margin-bottom: 15px;
  color: #333;
  border-bottom: 1px solid #eee;
  padding-bottom: 10px;
}

.modal-items-list {
  display: grid;
  gap: 15px;
}

.modal-item {
  display: flex;
  align-items: center;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
}

.modal-item-image {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 5px;
  margin-right: 15px;
}

.modal-item-details h5 {
  margin: 0 0 5px 0;
  color: #333;
}

.modal-item-details p {
  margin: 3px 0;
  color: #666;
  font-size: 14px;
}

.item-total {
  color: #84C551 !important;
  font-weight: 600 !important;
}

@media (max-width: 768px) {
  .orders-filters {
    flex-direction: column;
  }
  
  .search-input {
    min-width: 100%;
  }
  
  .orders-table {
    font-size: 12px;
  }
  
  .orders-table th,
  .orders-table td {
    padding: 8px 5px;
  }
  
  .order-info-grid {
    grid-template-columns: 1fr;
  }
  
  .modal-content {
    width: 95%;
  }
}
</style>