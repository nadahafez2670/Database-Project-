-- create data base--
CREATE DATABASE Online_Store

-- create customers table --
CREATE TABLE Customers(
    customer_id int PRIMARY KEY AUTO_INCREMENT,
    f_name varchar(50) NOT NULL,
    l_name varchar(50) NOT NULL,
    email varchar(50) UNIQUE NOT NULL,
    password varchar(100) NOT NULL,
    address varchar(50) NOT NULL 
)

-- create customer_phones table --
CREATE TABLE Customer_Phones(
    phone_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id int ,
    phone_number varchar(20) NOT NULL ,
    FOREIGN KEY (customer_id) REFERENCES Customers(customer_id)
)

--create cart table--
CREATE TABLE Cart (
cart_id int PRIMARY KEY AUTO_INCREMENT,
customer_id int ,
quantity int DEFAULT 0 ,
created_at DATETIME ,
FOREIGN KEY (customer_id) REFERENCES Customers(customer_id)
)

--create products table --
CREATE TABLE Products (
product_id int PRIMARY KEY AUTO_INCREMENT,
name varchar(100)  NOT NULL ,
sku varchar(50) UNIQUE ,
stock int DEFAULT 0,
cost decimal NOT NULL ,
retail_price decimal NOT NULL 
)

--create cart item table --
CREATE TABLE CartItem(
cart_item_id int PRIMARY KEY AUTO_INCREMENT,
cart_id int ,
product_id int ,
quantity int NOT NULL ,
FOREIGN KEY (cart_id) REFERENCES Cart(cart_id),
FOREIGN KEY (product_id) REFERENCES Products(product_id)
)

-- create orders table --
CREATE TABLE Orders(
order_id int PRIMARY KEY AUTO_INCREMENT,
customer_id int,
created_at DATETIME NOT NULL ,
total_price decimal NOT NULL ,
status varchar(20) NOT NULL ,
FOREIGN KEY (customer_id) REFERENCES Customers(customer_id)
)

-- create order item table--
CREATE TABLE OrderItem(
order_item_id int PRIMARY KEY AUTO_INCREMENT,
order_id int,
product_id int,
quantity int NOT NULL ,
price decimal NOT NULL,
 FOREIGN KEY (order_id) REFERENCES Orders(order_id),
 FOREIGN KEY (product_id) REFERENCES products(product_id)

)

-- create payment table --
CREATE TABLE Payment(
payment_id int PRIMARY KEY AUTO_INCREMENT,
customer_id int,
amount int NOT NULL,
method varchar(50) NOT NULL ,
date datetime NOT NULL ,
FOREIGN KEY (customer_id) REFERENCES Customers(customer_id)
)