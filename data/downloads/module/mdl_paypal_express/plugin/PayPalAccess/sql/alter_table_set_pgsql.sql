UPDATE dtb_customer SET kana01 = '' WHERE kana01 IS NULL;
UPDATE dtb_customer SET kana02 = '' WHERE kana02 IS NULL;
ALTER TABLE dtb_customer ALTER COLUMN kana01 SET NOT NULL;
ALTER TABLE dtb_customer ALTER COLUMN kana02 SET NOT NULL;
