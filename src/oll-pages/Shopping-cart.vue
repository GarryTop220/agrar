<template>
  <div class="checkout-page">
    <Menu />
    <div class="checkout-container">
      <div class="checkout-content">
        <h1 class="page-title">Оформлення замовлення</h1>
        
        <div class="checkout-grid">
          <!-- Ліва колонка - Інформація про користувача -->
          <div class="user-info-section">
            <div class="section-card">
              <h2 class="section-title">
                <i class="fas fa-user-circle icon"></i>
                Персональні дані
              </h2>
              <form @submit.prevent="handleSubmit" class="user-form">
                <div class="form-row">
                  <div class="form-group">
                    <label for="name">Ім'я*</label>
                    <input type="text" v-model="userData.name" id="name" required 
                           placeholder="Введіть ваше ім'я" class="form-input">
                  </div>
                  <div class="form-group">
                    <label for="surname">Прізвище*</label>
                    <input type="text" v-model="userData.surname" id="surname" required 
                           placeholder="Введіть ваше прізвище" class="form-input">
                  </div>
                </div>
                
                <div class="form-group">
                  <label for="phone">Телефон*</label>
                  <input type="tel" v-model="userData.phone" id="phone" required 
                         placeholder="+380 XX XXX XX XX" class="form-input">
                </div>
                
                <div class="form-group">
                  <label for="email">Email*</label>
                  <input type="email" v-model="userData.email" id="email" required 
                         placeholder="example@email.com" class="form-input">
                </div>
                
                <div class="form-group">
                  <label for="address">Адреса доставки*</label>
                  <input type="text" v-model="userData.address" id="address" required 
                         placeholder="Введіть повну адресу" class="form-input">
                </div>
              </form>
            </div>
          </div>
          
          <!-- Права колонка - Кошик та підсумок -->
          <div class="cart-section">
            <div class="section-card">
              <h2 class="section-title">
                <i class="fas fa-shopping-cart icon"></i>
                Ваше замовлення
              </h2>
              
              <div v-if="products.length === 0" class="empty-cart">
                <i class="fas fa-shopping-basket empty-icon"></i>
                <p>Ваш кошик порожній</p>
                <router-link to="/products" class="continue-shopping-btn">
                  Продовжити покупки
                </router-link>
              </div>
              
              <div v-else>
                <div class="cart-items">
                  <div v-for="(product, index) in products" :key="index" class="cart-item">
                    <div class="item-image-container">
                      <img :src="product.image" :alt="product.title" class="item-image">
                      <span class="item-quantity">{{ product.quantity }}</span>
                    </div>
                    <div class="item-details">
                      <h3 class="item-title">{{ product.title }}</h3>
                      <p class="item-price">{{ product.price }} грн × {{ product.quantity }}</p>
                      <div class="quantity-controls">
                        <button 
                          @click="decreaseQuantity(index)" 
                          class="quantity-btn"
                          :disabled="product.quantity <= 1"
                        >
                          <i class="fas fa-minus"></i>
                        </button>
                        <span class="quantity-value">{{ product.quantity }}</span>
                        <button 
                          @click="increaseQuantity(index)" 
                          class="quantity-btn"
                        >
                          <i class="fas fa-plus"></i>
                        </button>
                      </div>
                    </div>
                    <div class="item-actions">
                      <button @click="removeItem(index)" class="remove-btn">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                      <div class="item-total">
                        {{ (product.price * product.quantity).toFixed(2) }} грн
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="order-summary">
                  <div class="summary-row">
                    <span>Разом:</span>
                    <span class="summary-value">{{ calculateTotalAmount().toFixed(2) }} грн</span>
                  </div>
                  <div class="summary-row">
                    <span>Доставка:</span>
                    <span class="summary-value">За тарифами перевізника</span>
                  </div>
                  <div class="summary-row total">
                    <span>До сплати:</span>
                    <span class="summary-value">{{ calculateTotalAmount().toFixed(2) }} грн</span>
                  </div>
                </div>
                
                <button type="submit" @click="handleSubmit" class="submit-order-btn">
                  Підтвердити замовлення
                  <i class="fas fa-arrow-right"></i>
                </button>
                
                <p class="secure-payment-note">
                  <i class="fas fa-lock"></i>
                  Ваші дані захищені. Ми не зберігаємо дані платіжних карт.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <Footer />
  </div>
</template>

<script>
import Menu from '@/components/Menu.vue';
import Footer from '@/components/footer.vue';
import axios from 'axios';

export default {
  components: {
    Menu,
    Footer
  },
  data() {
    return {
      userData: {
        name: '',
        surname: '',
        phone: '',
        email: '',
        address: ''
      },
      products: [],
      currentToken: '',
      userId: ''
    };
  },
  created() {
    this.fetchUserData();
    this.fetchProductsInCart();
    this.currentToken = localStorage.getItem('token');
    this.userId = localStorage.getItem('user_id');
  },
  methods: {
    fetchUserData() {
      const userData = JSON.parse(localStorage.getItem('userData'));
      if (userData) {
        this.userData = userData;
      }
    },
    fetchProductsInCart() {
      const products = JSON.parse(localStorage.getItem('shoppingCart')) || [];
      this.products = products;
    },
    updateCart() {
      localStorage.setItem('shoppingCart', JSON.stringify(this.products));
    },
    increaseQuantity(index) {
      this.products[index].quantity++;
      this.updateCart();
    },
    decreaseQuantity(index) {
      if (this.products[index].quantity > 1) {
        this.products[index].quantity--;
        this.updateCart();
      }
    },
    removeItem(index) {
      this.products.splice(index, 1);
      this.updateCart();
      if (this.products.length === 0) {
        localStorage.removeItem('shoppingCart');
      }
    },
    handleSubmit() {
      if (!this.currentToken || !this.userId) {
        alert('Токен або ідентифікатор користувача не знайдено. Будь ласка, увійдіть в систему знову.');
        this.$router.push({ name: 'Login' });
        return;
      }

      const purchaseData = {
        token: this.currentToken,
        user_id: this.userId,
        name: this.userData.name,
        surname: this.userData.surname,
        phone: this.userData.phone,
        email: this.userData.email,
        address: this.userData.address,
        total_amount: this.calculateTotalAmount(),
        products: this.products.map(product => ({
          good_id: product.id,
          quantity: product.quantity,
          price: product.price
        }))
      };

      axios.post('http://localhost/agrar_shop/Backend/savePurchase.php', purchaseData, {
        withCredentials: true
      })
      .then(response => {
        if (response.data.success) {
          alert('Дані користувача та замовлення успішно збережені!');
          localStorage.removeItem('shoppingCart');
          this.products = [];
          this.$router.push({ name: 'OrderSuccess' });
        } else {
          alert('Помилка: ' + response.data.message);
        }
      })
      .catch(error => {
        console.error('Помилка збереження даних:', error);
        alert('Помилка збереження даних користувача та замовлення.');
      });
    },
    calculateTotalAmount() {
      return this.products.reduce((total, product) => total + (product.quantity * product.price), 0);
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

.checkout-page {
  font-family: 'Roboto', sans-serif;
  background-color: #f8f9fa;
  color: #333;
  line-height: 1.6;
}

.checkout-container {
  max-width: 1200px;
  margin: 30px auto;
  padding: 0 20px;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 30px;
  text-align: center;
}

.checkout-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 30px;
}

@media (min-width: 992px) {
  .checkout-grid {
    grid-template-columns: 1fr 1fr;
  }
}

.section-card {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  padding: 25px;
  margin-bottom: 30px;
}

.section-title {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 25px;
  color: #2c3e50;
  display: flex;
  align-items: center;
}

.icon {
  margin-right: 10px;
  color: #84C551;
  font-size: 22px;
}

.user-form {
  margin-top: 20px;
}

.form-row {
  display: flex;
  gap: 15px;
  margin-bottom: 15px;
}

.form-group {
  flex: 1;
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #555;
}

.form-input {
  padding: 12px 15px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 16px;
  transition: all 0.3s;
}

.form-input:focus {
  border-color: #84C551;
  box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
  outline: none;
}

.empty-cart {
  text-align: center;
  padding: 40px 20px;
}

.empty-icon {
  font-size: 50px;
  color: #ddd;
  margin-bottom: 20px;
}

.empty-cart p {
  font-size: 18px;
  color: #777;
  margin-bottom: 20px;
}

.continue-shopping-btn {
  display: inline-block;
  background-color: #84C551;
  color: white;
  padding: 12px 25px;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 500;
  transition: background-color 0.3s;
}

.continue-shopping-btn:hover {
  background-color: #73b141;
}

.cart-items {
  margin-bottom: 30px;
}

.cart-item {
  display: flex;
  align-items: center;
  padding: 15px 0;
  border-bottom: 1px solid #eee;
  position: relative;
}

.item-image-container {
  position: relative;
  margin-right: 15px;
}

.item-image {
  width: 70px;
  height: 70px;
  object-fit: cover;
  border-radius: 5px;
}

.item-quantity {
  position: absolute;
  top: -8px;
  right: -8px;
  background-color: #84C551;
  color: white;
  width: 25px;
  height: 25px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
}

.item-details {
  flex: 1;
}

.item-title {
  font-size: 16px;
  font-weight: 500;
  margin-bottom: 5px;
  color: #333;
}

.item-price {
  font-size: 14px;
  color: #777;
  margin-bottom: 10px;
}

.quantity-controls {
  display: flex;
  align-items: center;
  gap: 10px;
}

.quantity-btn {
  width: 30px;
  height: 30px;
  border: 1px solid #ddd;
  background: #f8f9fa;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.quantity-btn:hover {
  background: #e9ecef;
  border-color: #adb5bd;
}

.quantity-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.quantity-btn i {
  font-size: 12px;
  color: #555;
}

.quantity-value {
  min-width: 20px;
  text-align: center;
  font-weight: 500;
}

.item-actions {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 10px;
}

.remove-btn {
  background: none;
  border: none;
  color: #dc3545;
  cursor: pointer;
  font-size: 16px;
  transition: color 0.2s;
  padding: 5px;
}

.remove-btn:hover {
  color: #c82333;
}

.item-total {
  font-weight: 600;
  color: #2c3e50;
  font-size: 16px;
}

.order-summary {
  border-top: 1px solid #eee;
  padding-top: 20px;
  margin-bottom: 25px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 16px;
}

.summary-row.total {
  font-size: 18px;
  font-weight: 600;
  color: #2c3e50;
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #eee;
}

.summary-value {
  font-weight: 500;
}

.submit-order-btn {
  width: 100%;
  background-color: #84C551;
  color: white;
  border: none;
  padding: 15px;
  font-size: 16px;
  font-weight: 600;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.submit-order-btn:hover {
  background-color: #73b141;
}

.submit-order-btn i {
  margin-left: 10px;
}

.secure-payment-note {
  font-size: 13px;
  color: #777;
  text-align: center;
  margin-top: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.secure-payment-note i {
  margin-right: 8px;
  color: #84C551;
}

@media (max-width: 768px) {
  .form-row {
    flex-direction: column;
    gap: 0;
  }
  
  .checkout-container {
    padding: 0 15px;
  }
  
  .section-card {
    padding: 20px 15px;
  }
  
  .cart-item {
    flex-wrap: wrap;
  }
  
  .item-actions {
    flex-direction: row;
    width: 100%;
    justify-content: space-between;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px dashed #eee;
  }
}
</style>