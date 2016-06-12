INSERT INTO `#__jshopping_payment_method` (`payment_id`, `name_en-GB`, `name_fa-IR`, `description_en-GB`, `description_fa-IR`, `payment_code`, `payment_class`, `payment_publish`, `payment_ordering`, `payment_params`, `payment_type`, `tax_id`, `price`, `show_descr_in_email`) VALUES
(NULL, 'Zarinpal Secure Payment','درگاه پرداخت زرین پال', 'Zarinpal Secure Payment Gateway', 'با زرین پال امن و سریع پرداخت کنید!', 'zarinpal','pm_zarinpal', 0, 1, 'merchant_id=a1b2c3d4\n testmode=1\n gate_type=0\n currency=0\n transaction_end_status=6\n transaction_pending_status=1\n transaction_failed_status=3\n', 2, 0, 0, 0);

CREATE TABLE IF NOT EXISTS `#__jshopping_zarinpal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(225) NOT NULL,
  `ref_id` varchar(225) NOT NULL,
  `amount` VARCHAR(25) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
