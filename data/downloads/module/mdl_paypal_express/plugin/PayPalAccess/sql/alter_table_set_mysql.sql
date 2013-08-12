UPDATE dtb_customer SET kana01 = '' WHERE kana01 IS NULL;
UPDATE dtb_customer SET kana02 = '' WHERE kana02 IS NULL;
ALTER TABLE dtb_customer MODIFY COLUMN kana01 TEXT NOT NULL;
ALTER TABLE dtb_customer MODIFY COLUMN kana02 TEXT NOT NULL;
