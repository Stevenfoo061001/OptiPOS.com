<?php
require_once __DIR__ . "/db.php";

$sql = <<<SQL
-- cashier
CREATE TABLE IF NOT EXISTS users (
    userid VARCHAR(7) PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    phone VARCHAR (15) UNIQUE NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(10) NOT NULL CHECK (role IN ('admin', 'cashier'))
);

-- member
CREATE TABLE IF NOT EXISTS member (
    memberid VARCHAR(7) PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(50) UNIQUE,
    dateissued DATE NOT NULL DEFAULT CURRENT_DATE,
    dateexpired DATE NOT NULL DEFAULT (CURRENT_DATE + INTERVAL '1 year'),
    points INT NOT NULL DEFAULT 0 CHECK (points >= 0)
);

-- orders
CREATE TABLE IF NOT EXISTS orders (
    orderid VARCHAR(7) PRIMARY KEY,
    orderdate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    subtotal DECIMAL(8,2) NOT NULL,
    tax DECIMAL(8,2) NOT NULL,
    discount DECIMAL(8,2) NOT NULL,
    grandtotal DECIMAL(8,2) NOT NULL,
    memberid VARCHAR(7),

    CONSTRAINT fk_order_member
        FOREIGN KEY (memberid)
        REFERENCES member(memberid)
        ON DELETE SET NULL

);

-- transactions
CREATE TABLE IF NOT EXISTS transactions (
    transactionid VARCHAR(7) PRIMARY KEY,
    orderid VARCHAR(7) NOT NULL,
    userid VARCHAR(7),
    paymentmethod VARCHAR(20) NOT NULL,
    amountpaid DECIMAL(8,2) NOT NULL,
    payment_date DATE NOT NULL DEFAULT CURRENT_DATE,
    payment_time TIME NOT NULL DEFAULT CURRENT_TIME,
    status VARCHAR(20) DEFAULT 'PAID',

    CONSTRAINT fk_tx_order
        FOREIGN KEY (orderid)
        REFERENCES orders(orderid)
        ON DELETE CASCADE,

    CONSTRAINT fk_tx_user
        FOREIGN KEY (userid)
        REFERENCES users(userid)
        ON DELETE SET NULL

);

-- stock
CREATE TABLE IF NOT EXISTS stock (
    stockid VARCHAR(7) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    unitprice DECIMAL(8,2) NOT NULL CHECK (unitprice >= 0),
    quantity INT NOT NULL CHECK (quantity >= 0),
    category VARCHAR(30),
    adminid VARCHAR(7),

    CONSTRAINT fk_stock_admin
        FOREIGN KEY (adminid)
        REFERENCES users(userid)
        ON DELETE SET NULL
);

-- order_item
CREATE TABLE IF NOT EXISTS order_item (
    orderid VARCHAR(7) NOT NULL,
    stockid VARCHAR(7) NOT NULL,
    unitprice DECIMAL(8,2) NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),

    PRIMARY KEY (orderid, stockid),

    CONSTRAINT fk_item_order
        FOREIGN KEY (orderid)
        REFERENCES orders(orderid)
        ON DELETE CASCADE,

    CONSTRAINT fk_item_stock
        FOREIGN KEY (stockid)
        REFERENCES stock(stockid)
);
SQL;

$pdo->exec($sql);
