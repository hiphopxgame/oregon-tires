-- Migration 019: Gallery bilingual support (EN/ES)
ALTER TABLE oretir_gallery_images
    CHANGE COLUMN title title_en VARCHAR(200) DEFAULT NULL,
    CHANGE COLUMN description description_en TEXT DEFAULT NULL,
    ADD COLUMN title_es VARCHAR(200) DEFAULT NULL AFTER title_en,
    ADD COLUMN description_es TEXT DEFAULT NULL AFTER description_en;
