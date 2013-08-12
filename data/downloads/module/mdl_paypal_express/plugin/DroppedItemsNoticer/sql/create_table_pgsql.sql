CREATE TABLE plg_droppeditemsnoticer_order (
    order_id int NOT NULL,
    complete_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (order_id)
);
CREATE TABLE plg_droppeditemsnoticer_auth (
    authcode TEXT NOT NULL,
    product_class_id int NOT NULL,
    customer_id int NOT NULL,
    create_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (authcode)
);
