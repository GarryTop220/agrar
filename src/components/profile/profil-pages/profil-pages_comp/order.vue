<template>
  <div class="orders-container">
    <div class="orders-header">
      <h2 class="orders-title">
        <img :src="Xz" alt="" class="xz"> Поточні замовлення <img :src="Xz_2" alt="" class="xz">
      </h2>
    </div>
    
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Завантаження замовлень...</p>
    </div>
    
    <div v-else-if="orders.length === 0" class="no-orders">
      <div class="no-orders-icon">📦</div>
      <h3>У вас поки немає замовлень</h3>
      <p>Коли ви зробите замовлення, воно з'явиться тут</p>
      <router-link to="/" class="shop-now-btn">Почати покупки</router-link>
    </div>
    
    <div v-else class="orders-list">
      <div v-for="order in orders" :key="order.id" class="order-card">
        <div class="order-header">
          <div class="order-info">
            <h3 class="order-number">Замовлення №{{ order.id }}</h3>
            <p class="order-date">{{ formatDate(order.created_at) }}</p>
          </div>
          <div class="order-status">
            <span class="status-badge processing">В обробці</span>
          </div>
        </div>
        
        <div class="order-details">
          <div class="delivery-info">
            <h4>Інформація про доставку:</h4>
            <p><strong>Отримувач:</strong> {{ order.name }} {{ order.surname }}</p>
            <p><strong>Телефон:</strong> {{ order.phone }}</p>
            <p><strong>Адреса:</strong> {{ order.address }}</p>
          </div>
          
          <div class="order-items">
            <h4>Товари в замовленні:</h4>
            <div class="items-list">
              <div v-for="item in order.items" :key="item.good_id" class="order-item">
                <img :src="item.image" :alt="item.title" class="item-image">
                <div class="item-details">
                  <h5>{{ item.title }}</h5>
                  <p>{{ item.quantity }} шт. × {{ item.price }} грн</p>
                </div>
                <div class="item-total">
                  {{ (item.quantity * item.price).toFixed(2) }} грн
                </div>
              </div>
            </div>
          </div>
          
          <div class="order-total">
            <strong>Загальна сума: {{ order.total_amount }} грн</strong>
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
      loading: true
    };
  },
  mounted() {
    this.fetchOrders();
  },
  methods: {
    async fetchOrders() {
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
          console.error('Помилка отримання замовлень:', response.data.message);
        }
      } catch (error) {
        console.error('Помилка запиту:', error);
      } finally {
        this.loading = false;
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
.orders-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.orders-title {
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

.no-orders {
  text-align: center;
  padding: 60px 20px;
  background: #f8f9fa;
  border-radius: 10px;
}

.no-orders-icon {
  font-size: 60px;
  margin-bottom: 20px;
}

.no-orders h3 {
  color: #333;
  margin-bottom: 10px;
}

.no-orders p {
  color: #666;
  margin-bottom: 30px;
}

.shop-now-btn {
  display: inline-block;
  background-color: #84C551;
  color: white;
  padding: 12px 30px;
  border-radius: 25px;
  text-decoration: none;
  font-weight: 500;
  transition: background-color 0.3s;
}

.shop-now-btn:hover {
  background-color: #73b141;
}

.order-card {
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  margin-bottom: 20px;
  overflow: hidden;
}

.order-header {
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

.status-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
}

.status-badge.processing {
  background-color: #fff3cd;
  color: #856404;
}

.order-details {
  padding: 20px;
}

.delivery-info {
  margin-bottom: 25px;
}

.delivery-info h4 {
  margin-bottom: 10px;
  color: #333;
}

.delivery-info p {
  margin: 5px 0;
  color: #666;
}

.order-items h4 {
  margin-bottom: 15px;
  color: #333;
}

.items-list {
  margin-bottom: 20px;
}

.order-item {
  display: flex;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid #f0f0f0;
}

.order-item:last-child {
  border-bottom: none;
}

.item-image {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 5px;
  margin-right: 15px;
}

.item-details {
  flex: 1;
}

.item-details h5 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 14px;
}

.item-details p {
  margin: 0;
  color: #666;
  font-size: 12px;
}

.item-total {
  font-weight: 600;
  color: #84C551;
}

.order-total {
  text-align: right;
  padding-top: 15px;
  border-top: 1px solid #eee;
  font-size: 18px;
  color: #333;
}
</style>