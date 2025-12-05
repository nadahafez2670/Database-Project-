<?php
session_start();
include "db.php"; 

header('Content-Type: application/json');

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
if (!empty($input)) {
    $_POST = array_merge($_POST, $input);
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// --- 1. Add to Cart ---
if ($action == 'add_to_cart') {
    $product_id = $_POST['product_id'];

    if (!isset($_SESSION['current_cart_id'])) {
        $dateNow = date('Y-m-d H:i:s');
        mysqli_query($conn, "INSERT INTO cart (customer_id, quantity, created_at) VALUES ('$user_id', 0, '$dateNow')");
        $_SESSION['current_cart_id'] = mysqli_insert_id($conn);
    }
    
    $cart_id = $_SESSION['current_cart_id'];

    $checkItem = mysqli_query($conn, "SELECT cart_item_id FROM cartitem WHERE cart_id = '$cart_id' AND product_id = '$product_id'");
    
    if (mysqli_num_rows($checkItem) > 0) {
        mysqli_query($conn, "UPDATE cartitem SET quantity = quantity + 1 WHERE cart_id = '$cart_id' AND product_id = '$product_id'");
    } else {
        mysqli_query($conn, "INSERT INTO cartitem (cart_id, product_id, quantity) VALUES ('$cart_id', '$product_id', 1)");
    }
    
    updateCartTotal($conn, $cart_id);
    echo json_encode(['status' => 'success']);
}

// --- 2. Update Quantity (+ / -) ---
elseif ($action == 'update_cart_item') {
    if (!isset($_SESSION['current_cart_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'No active cart']);
        exit();
    }

    $cart_id = $_SESSION['current_cart_id'];
    $product_id = $_POST['product_id'];
    $operator = $_POST['operator']; 

    $checkItem = mysqli_query($conn, "SELECT quantity FROM cartitem WHERE cart_id = '$cart_id' AND product_id = '$product_id'");
    
    if ($row = mysqli_fetch_assoc($checkItem)) {
        $currentQty = $row['quantity'];

        if ($operator == 'increase') {
            mysqli_query($conn, "UPDATE cartitem SET quantity = quantity + 1 WHERE cart_id = '$cart_id' AND product_id = '$product_id'");
        } 
        elseif ($operator == 'decrease') {
            if ($currentQty > 1) {
                mysqli_query($conn, "UPDATE cartitem SET quantity = quantity - 1 WHERE cart_id = '$cart_id' AND product_id = '$product_id'");
            } else {
                mysqli_query($conn, "DELETE FROM cartitem WHERE cart_id = '$cart_id' AND product_id = '$product_id'");
            }
        }
        
        updateCartTotal($conn, $cart_id);
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found']);
    }
}

// --- 3. Checkout ---
elseif ($action == 'checkout') {
    if (!isset($_SESSION['current_cart_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'No active cart']);
        exit();
    }

    $cart_id = $_SESSION['current_cart_id'];
    $payment_method = $_POST['payment_method'] ?? 'Cash';

    $itemsQuery = mysqli_query($conn, "
        SELECT ci.*, p.retail_price, p.stock 
        FROM cartitem ci 
        JOIN products p ON ci.product_id = p.product_id 
        WHERE ci.cart_id = '$cart_id'
    ");
    
    $total_price = 0;
    $order_items = [];

    while ($item = mysqli_fetch_assoc($itemsQuery)) {
        if ($item['stock'] < $item['quantity']) {
            echo json_encode(['status' => 'error', 'message' => 'Out of stock: Product ID ' . $item['product_id']]);
            exit();
        }
        $total_price += $item['retail_price'] * $item['quantity'];
        $order_items[] = $item;
    }

    if (count($order_items) > 0) {
        $dateNow = date('Y-m-d H:i:s');
        
        $insertOrder = "INSERT INTO orders (customer_id, total_price, status, created_at) 
                        VALUES ('$user_id', '$total_price', 'Complete', '$dateNow')";
        
        if (mysqli_query($conn, $insertOrder)) {
            $order_id = mysqli_insert_id($conn);

            mysqli_query($conn, "INSERT INTO payment (customer_id, amount, method, date) 
                                 VALUES ('$user_id', '$total_price', '$payment_method', '$dateNow')");

            foreach ($order_items as $item) {
                $pid = $item['product_id'];
                $qty = $item['quantity'];
                $price = $item['retail_price'];
                
                mysqli_query($conn, "INSERT INTO orderitem (order_id, product_id, quantity, price) 
                                     VALUES ('$order_id', '$pid', '$qty', '$price')");
                
                mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE product_id = '$pid'");
            }

            unset($_SESSION['current_cart_id']);
            $_SESSION['last_success_order'] = $order_id;

            echo json_encode(['status' => 'success', 'order_id' => $order_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['status' => 'empty']);
    }
}

// --- 4. Cancel Order ---
elseif ($action == 'cancel_order') {
    $order_id = $_POST['order_id'];
    
    $checkOwner = mysqli_query($conn, "SELECT order_id FROM orders WHERE order_id = '$order_id' AND customer_id = '$user_id'");
    
    if (mysqli_num_rows($checkOwner) > 0) {
        mysqli_query($conn, "UPDATE orders SET status = 'Cancelled' WHERE order_id = '$order_id'");
        
        $itemsQuery = mysqli_query($conn, "SELECT product_id, quantity FROM orderitem WHERE order_id = '$order_id'");
        while ($item = mysqli_fetch_assoc($itemsQuery)) {
            $pid = $item['product_id'];
            $qty = $item['quantity'];
            mysqli_query($conn, "UPDATE products SET stock = stock + $qty WHERE product_id = '$pid'");
        }
        
        echo json_encode(['status' => 'cancelled']);
    }
}

function updateCartTotal($conn, $cart_id) {
    $sumQuery = mysqli_query($conn, "SELECT SUM(quantity) as total_qty FROM cartitem WHERE cart_id = '$cart_id'");
    $data = mysqli_fetch_assoc($sumQuery);
    $totalQty = $data['total_qty'] ?? 0;
    mysqli_query($conn, "UPDATE cart SET quantity = '$totalQty' WHERE cart_id = '$cart_id'");
}
?>