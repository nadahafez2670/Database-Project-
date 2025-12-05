<?php
session_start();
include "db.php"; 

$user_id = $_SESSION['user_id'] ?? null; 
$user_name = "Guest";

if ($user_id) {
    $uQuery = mysqli_query($conn, "SELECT f_name FROM customers WHERE customer_id = '$user_id'");
    if ($uQuery && $uRow = mysqli_fetch_assoc($uQuery)) {
        $user_name = $uRow['f_name'];
    }
}

$show_success_popup = false;
$last_order_id = null;
if (isset($_SESSION['last_success_order'])) {
    $show_success_popup = true;
    $last_order_id = $_SESSION['last_success_order'];
    unset($_SESSION['last_success_order']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Neon Store</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"/>
<style>

body { 
  background: linear-gradient(135deg, #0c080d, #1f1823, #382b3f); 
  font-family: Arial, sans-serif; 
  margin: 0; 
  color: #d6d3e5; 
  min-height: 100vh;
}

::-webkit-scrollbar {
  width: 6px; 
}
::-webkit-scrollbar-track {
  background: transparent; 
}
::-webkit-scrollbar-thumb {
  background: #7a6284; 
  border-radius: 20px; 
}
::-webkit-scrollbar-thumb:hover {
  background: #d6d3e5; 
}

header { 
  background-color: #1f1823; 
  padding: 15px 40px; 
  display: flex; 
  justify-content: space-between; 
  align-items: center; 
  box-shadow: 0 4px 15px rgba(0,0,0,0.5); 
  position: sticky; 
  top: 0; 
  z-index: 100; 
  border-bottom: 1px solid #382b3f;
}

.logo { 
  font-size: 1.5em; 
  font-weight: bold; 
  color: #fff; 
  text-shadow: 0 0 10px #7a6284; 
}

nav a { 
  color: #d6d3e5; 
  margin: 0 15px; 
  text-decoration: none; 
  transition: 0.3s; 
  font-weight: bold;
}
nav a:hover {
  color: #7a6284;
  text-shadow: 0 0 5px #7a6284;
}

.cart-btn { 
  background: transparent; 
  border: none; 
  color: #d6d3e5; 
  font-size: 1.4em; 
  cursor: pointer; 
  position: relative; 
}

#cart-count { 
  background: #7a6284; 
  color: white; 
  border-radius: 50%; 
  padding: 2px 6px; 
  font-size: 0.6em; 
  position: absolute; 
  top: -2px; 
  right: -2px; 
  box-shadow: 0 0 5px #7a6284;
}

.products-container { 
  display: flex; 
  flex-wrap: wrap; 
  justify-content: center; 
  gap: 30px; 
  padding: 60px 20px; 
}

.product-card { 
  background: #1f1823; 
  width: 260px; 
  padding: 25px; 
  border-radius: 15px; 
  box-shadow: 0 0 20px #7a6284; 
  text-align: center; 
  transition: 0.3s; 
}

.product-card:hover { 
  transform: translateY(-5px); 
  box-shadow: 0 0 30px #d6d3e5; 
}

.product-image-box { 
  height: 150px; 
  border-radius: 10px; 
  margin-bottom: 20px; 
  background: linear-gradient(135deg, #382b3f, #7a6284); 
  box-shadow: inset 0 0 10px rgba(0,0,0,0.5); 
}

h3 { 
  margin: 10px 0; 
  font-size: 1em; 
  color: #d6d3e5; 
  font-weight: bold; 
  text-shadow: 0 0 5px #7a6284;
}

.price { 
  color: #fff; 
  font-size: 1.1em; 
  font-weight: bold; 
  margin-bottom: 20px; 
  display: block; 
}

.buy-btn, .main-checkout-btn, .confirm-btn-yes { 
  background: #7a6284; 
  color: white; 
  border: none; 
  padding: 12px 30px; 
  border-radius: 30px; 
  cursor: pointer; 
  box-shadow: 0 0 15px #7a6284; 
  transition: 0.3s; 
  font-weight: bold;
}

.buy-btn:hover, .main-checkout-btn:hover, .confirm-btn-yes:hover { 
  background: #d6d3e5; 
  color: #1f1823; 
}

.sidebar-cart { 
  position: fixed; 
  top: 0; 
  right: 0; 
  width: 340px; 
  height: 100%; 
  background-color: #1f1823; 
  box-shadow: -5px 0 20px rgba(0,0,0,0.5); 
  padding: 20px; 
  transform: translateX(100%); 
  transition: 0.4s; 
  z-index: 999; 
  overflow-y: auto; 
  border-left: 1px solid #382b3f; 
}

.sidebar-cart.active { 
  transform: translateX(0); 
}

.cart-header { 
  display: flex; 
  justify-content: space-between; 
  align-items: center; 
  border-bottom: 1px solid #382b3f; 
  padding-bottom: 15px; 
  margin-bottom: 20px; 
}

.cart-item { 
  display: flex; 
  justify-content: space-between; 
  align-items: center; 
  margin-bottom: 15px; 
  border-bottom: 1px dashed #52425c; 
  padding-bottom: 10px; 
  color: #d6d3e5; 
}

.qty-controls { 
  display: flex; 
  align-items: center; 
  gap: 8px; 
  background: #382b3f; 
  padding: 5px; 
  border-radius: 5px; 
  box-shadow: 0 0 5px #52425c inset;
}

.qty-btn { 
  background-color: #7a6284; 
  color: white; 
  border: none; 
  width: 25px; 
  height: 25px; 
  border-radius: 50%; 
  cursor: pointer; 
  font-weight: bold; 
}

.qty-btn:hover { 
  background-color: #d6d3e5; 
  color: #000; 
}

.cart-qty { 
  font-weight: bold; 
  min-width: 20px; 
  text-align: center; 
  color: white; 
}

.payment-selection { 
  display: flex; 
  flex-direction: column; 
  gap: 15px; 
  margin-top: 20px; 
  margin-bottom: 20px; 
  background: #382b3f; 
  padding: 15px; 
  border-radius: 10px; 
  box-shadow: 0 0 10px #52425c inset;
}

.radio-item { 
  display: flex; 
  align-items: center; 
  cursor: pointer; 
  color: #d6d3e5; 
  font-size: 16px; 
}

.radio-item input[type="radio"] { 
  accent-color: #7a6284; 
  width: 18px; 
  height: 18px; 
  margin-right: 10px; 
  cursor: pointer; 
}


.popup { 
  position: fixed; 
  inset: 0; 
  background: rgba(12, 8, 13, 0.9); 
  display: flex; 
  justify-content: center; 
  align-items: center; 
  z-index: 2000; 
  display: none; 
}

.popup-box { 
  background: #1f1823; 
  padding: 40px; 
  border-radius: 20px; 
  text-align: center; 
  box-shadow: 0 0 30px #7a6284; 
  width: 320px; 
  animation: glow 1.5s infinite alternate; 
}

@keyframes glow {
  from {
    box-shadow: 0 0 15px #52425c;
  }
  to {
    box-shadow: 0 0 30px #7a6284;
  }
}

.cancel-btn, .confirm-btn-no { 
  background-color: #382b3f; 
  color: #d6d3e5;
  border: none;
  padding: 12px 30px;
  border-radius: 30px;
  cursor: pointer;
  margin-top: 10px;
  font-weight: bold;
}

.cancel-btn:hover, .confirm-btn-no:hover { 
  background-color: #52425c; 
  color: white;
}

.main-checkout-btn { 
  width: 100%; 
  margin-top: 10px;
}
</style>

</head>

<body>

<header>
  <div class="logo">Neon Store</div>
  <nav>
    <a href="#">Home</a>
    <?php if($user_id): ?>
        <span style="margin:0 10px;">Hi, <?php echo htmlspecialchars($user_name); ?></span>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="Log.html">Login</a>
    <?php endif; ?>
  </nav>
  <button id="cart-icon" class="cart-btn" onclick="toggleCart()">
    <i class="fas fa-shopping-cart"></i>
    <?php
    $count = 0;
    if (isset($_SESSION['current_cart_id'])) {
        $cid = $_SESSION['current_cart_id'];
        $res = mysqli_query($conn, "SELECT SUM(quantity) as qty FROM cartitem WHERE cart_id = '$cid'");
        if($res && $row = mysqli_fetch_assoc($res)) { $count = $row['qty'] ?? 0; }
    }
    ?>
    <span id="cart-count"><?php echo $count; ?></span>
  </button>
</header>

<main class="products-container">
    <?php
    $products = mysqli_query($conn, "SELECT * FROM products");
    if($products && mysqli_num_rows($products) > 0) {
        while($p = mysqli_fetch_assoc($products)):
    ?>
    <div class="product-card">
        <div class="product-image-box"></div>
        <h3><?php echo htmlspecialchars($p['name']); ?></h3>
        <span class="price"><?php echo $p['retail_price']; ?> SAR</span>
        <button class="buy-btn" onclick="addToCart(<?php echo $p['product_id']; ?>)">Add to Cart</button>
    </div>
    <?php endwhile; } else { echo "<p style='text-align:center;'>No products available.</p>"; } ?>
</main>

<div class="sidebar-cart" id="sidebar-cart">
    <div class="cart-header">
        <h3>Shopping Cart</h3>
        <button onclick="toggleCart()" style="background:none;border:none;color:white;font-size:1.5em;cursor:pointer;">&times;</button>
    </div>
    <div id="cart-content">
    <?php
    if (isset($_SESSION['current_cart_id'])) {
        $cart_id = $_SESSION['current_cart_id'];
        $cartItems = mysqli_query($conn, "SELECT ci.product_id, ci.quantity, p.name, p.retail_price FROM cartitem ci JOIN products p ON ci.product_id = p.product_id WHERE ci.cart_id = '$cart_id'");

        if ($cartItems && mysqli_num_rows($cartItems) > 0) {
            $total = 0;
            while ($row = mysqli_fetch_assoc($cartItems)) {
                $total += $row['retail_price'] * $row['quantity'];
                echo "<div class='cart-item'>";
                echo "<div class='item-info'><strong>" . $row['name'] . "</strong><br><small style='color:#BE9EC9'>" . $row['retail_price'] . " SAR</small></div>";
                echo "<div class='qty-controls'>";
                echo "<button class='qty-btn' onclick='event.stopPropagation(); updateCartItem(".$row['product_id'].", \"decrease\")'>-</button>";
                echo "<span class='cart-qty'>" . $row['quantity'] . "</span>";
                echo "<button class='qty-btn' onclick='event.stopPropagation(); updateCartItem(".$row['product_id'].", \"increase\")'>+</button>";
                echo "</div></div>";
            }
            echo "<hr style='border-color:#3E2C46; margin: 15px 0;'><p style='font-size:1.2em; margin-bottom:15px;'><strong>Total: $total SAR</strong></p>";
            echo '<div class="payment-selection">
                    <label class="radio-item"><input type="radio" name="payment_method" value="COD" checked><span>Cash on Delivery</span></label>
                    <label class="radio-item"><input type="radio" name="payment_method" value="VISA"><span>Visa / MasterCard</span></label>
                  </div>
                  <button class="main-checkout-btn" onclick="checkout()">Checkout</button>';
        } else { echo "<p style='text-align:center; color:#999; margin-top:20px;'>Cart is empty</p>"; }
    } else { echo "<p style='text-align:center; color:#999; margin-top:20px;'>Cart is empty</p>"; }
    ?>
    </div>
</div>

<div class="popup" id="success-popup">
  <div class="popup-box">
    <h2 style="color:#DDA0DD">✅ Order Placed!</h2>
    <p>Order ID: #<span id="order-id-display"></span></p>
    <p>Your order was successful.</p>
    
    <button class="buy-btn" onclick="this.closest('.popup').style.display='none'; closePopupFn()">Close</button>
    
    <button class="buy-btn cancel-btn" onclick="this.closest('.popup').style.display='none'; openCancelModal()">Cancel Order</button>
  
  </div>
</div>

<div class="popup" id="cancel-confirm-popup">
  <div class="popup-box">
    <h2 style="color:#E0D0E8">⚠️ Confirm Cancellation</h2>
    <p>Are you sure you want to cancel this order?</p>
    <button class="confirm-btn-yes" onclick="finalCancelOrder()">Yes, Cancel</button>
    <button class="confirm-btn-no" onclick="closeCancelModal()">No, Keep it</button>
  </div>
</div>

<div class="popup" id="cancelled-popup">
  <div class="popup-box">
    <i class="fas fa-times-circle" style="font-size: 50px; color: #ff6b6b; margin-bottom: 15px;"></i>
    <h2 style="color:#ff6b6b; margin-top: 0;">Order Cancelled</h2>
    <p>Your order has been cancelled successfully.</p>
    <small style="color:#aaa;">Refreshing page in 5 seconds...</small>
  </div>
</div>

<script>
let currentOrderId = <?php echo $last_order_id ?? 'null'; ?>;

function toggleCart() {
    document.getElementById("sidebar-cart").classList.toggle("active");
}

function closePopupFn() {
    document.querySelectorAll('.popup').forEach(p => p.style.display = 'none');
    document.getElementById('cart-content').innerHTML = "<p style='text-align:center; color:#999; margin-top:20px;'>Cart is empty</p>";
    document.getElementById('cart-count').textContent = "0";
}

function openCancelModal() {
    
    document.getElementById('success-popup').style.display = 'none';
    document.getElementById('cancel-confirm-popup').style.display = 'flex';
}

function closeCancelModal() {
    document.getElementById('cancel-confirm-popup').style.display = 'none';
    document.getElementById('success-popup').style.display = 'flex';
}

function addToCart(id) {
    const formData = new FormData();
    formData.append('action', 'add_to_cart');
    formData.append('product_id', id);
    fetch('api.php', { method: 'POST', body: formData }).then(res => res.json()).then(data => {
        if(data.status === 'success') location.reload();
        else alert(data.message);
    });
}

function updateCartItem(id, operator) {
    const formData = new FormData();
    formData.append('action', 'update_cart_item');
    formData.append('product_id', id);
    formData.append('operator', operator);
    fetch('api.php', { method: 'POST', body: formData }).then(res => res.json()).then(data => {
        if(data.status === 'success') location.reload();
    });
}

function checkout() {
    const methodElem = document.querySelector('input[name="payment_method"]:checked');
    if(!methodElem) return; 
    const method = methodElem.value;
    const formData = new FormData();
    formData.append('action', 'checkout');
    formData.append('payment_method', method);

    fetch('api.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            currentOrderId = data.order_id;
            document.getElementById('order-id-display').textContent = currentOrderId;
            document.getElementById('success-popup').style.display = 'flex';
        }
    });
}


function finalCancelOrder() {
    if(!currentOrderId) return;

    
    document.querySelectorAll('.popup').forEach(p => p.style.display = 'none');

    const formData = new FormData();
    formData.append('action', 'cancel_order');
    formData.append('order_id', currentOrderId);

    fetch('api.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'cancelled' || data.status === 'success') {
            document.getElementById('cancelled-popup').style.display = 'flex';
            setTimeout(function() {
                location.reload();
            }, 5000);
        } else {
            
            document.getElementById('cancel-confirm-popup').style.display = 'flex';
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

<?php if(isset($_SESSION['current_cart_id']) && !$show_success_popup): ?>
    
<?php endif; ?>

</script>

</body>
</html>