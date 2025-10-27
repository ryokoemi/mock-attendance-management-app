# 勤怠管理アプリ
## 環境構築
#### Dockerビルド
1. ```git clone git@github.com:ryokoemi/mock-flea-market-app.git```
2. DockerDesktopアプリを立ち上げる
3. ```docker-compose up -d --build```
MacのM1・M2チップのPCの場合、no matching manifest for linux/arm64/v8 in the manifest list entriesのメッセージが表示されビルドができないことがあります。 エラーが発生する場合は、docker-compose.ymlファイルの「mysql」内に「platform」の項目を追加で記載してください
```
mysql:
    platform: linux/x86_64(この文を追加)
    image: mysql:8.0.26
    environment:
```
#### Laravel環境構築
1. ```docker-compose exec php bash```
2. ```composer install```
3. 「.env.example」ファイルを 「.env」ファイルに命名を変更。
4. .envに以下の環境変数を追加
```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```
5. アプリケーションキーの作成
```
php artisan key:generate
```
6. マイグレーションの実行
```
php artisan migrate
```
7. シーディングの実行
```
php artisan db:seed
```
8. storageディレクトリへのシンボリックリンク作成
```
php artisan storage:link
```
## 使用技術（実行環境）
- PHP8.4.8
- Laravel8.83.8
- MySQL8.0.26

## ER図
![画像](https://coachtech-lms-bucket.s3.ap-northeast-1.amazonaws.com/question/20251016172414_%E3%82%B9%E3%82%AF%E3%83%AA%E3%83%BC%E3%83%B3%E3%82%B7%E3%83%A7%E3%83%83%E3%83%88+2025-10-16%2016.29.54.png)
## URL
- 開発環境：http://localhost/
- phpMyAdmin:：http://localhost:8080/

