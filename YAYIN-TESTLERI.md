# Vercel canlı kontrolü — 2 Kasım 2026

Adres: https://mailsystem-nine.vercel.app/

PHP 8.5 topluluk runtime'ı ve ayrı ücretsiz Neon PostgreSQL kaynağı (`mail_system_db`) kullanılıyor. Kaynak kod GitHub `senayasko/mailsystem` deposundaki `main` dalından otomatik dağıtılıyor. Aksesuar sitesinin verileri kullanılmadı.

Canlı HTTP kontrolünde kayıt/giriş, oturum kimliği yenileme, çerez ayarları, mesaj gönderme ve listeleme, JSON yıldızlama/okunmamış sayaçları, HTML escaping, dosya yükleme/indirme, yetkisiz mesaj ve ek erişiminin reddi, CSRF kontrolü, TR/EN, geçersiz dosya türünün reddi ve çöp kutusuna taşıma başarılı oldu. Tekli geri alma isteği de başarılı sonuç verdi. Sonraki otomatik istekler Vercel Security Checkpoint tarafından 403 ile durduruldu; HTTP testinin tamamı için başarı iddiası yoktur.

Normal tarayıcı doğrulaması otomatik tamamlandı. Tarayıcıda ayrı test hesabı oluşturuldu; giriş, formdan mesaj gönderme, toplu silme, çöp kutusundan toplu geri alma ve gelen kutusunda yeniden görünme doğrulandı. Test mesajı kendine gönderildiği için gönderen ve alıcı silme işaretlerinin birlikte geri alınması da denendi.

Yerel XAMPP HTTP kontrolü değişikliklerden sonra başarılı oldu. Canlı sürümde kullanıcıların kayıt ekranından yeni hesap oluşturması gerekir. Yerel kullanıcılar ve dosyalar otomatik taşınmadı.
