SELECT b.id_bantuan, b.action_at, b.status , b.prioritas 
FROM bantuan b JOIN kategori k ON (b.id_kategori = k.id_kategori) 
WHERE b.blokir IS NULL AND UPPER(b.status) = UPPER('D') AND LOWER(k.nama) = LOWER('pojok peduli berbagi') 
AND (b.action_at > (SELECT MIN(action_at) FROM bantuan WHERE id_bantuan IN (7, 1, 2) AND status = 'D'))
ORDER BY b.prioritas DESC, b.action_at DESC