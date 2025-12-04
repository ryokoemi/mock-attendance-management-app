# 勤怠管理アプリ
## 環境構築
#### Dockerビルド
1. ```git clone git@github.com:ryokoemi/mock-attendance-management-app.git```
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

※factoriesとseedersに格納しているすべてのファクトリと、シーダーファイルを使います。
src/database/factories/AttendanceFactory.php
src/database/factories/BreakTimeFactory.php
src/database/factories/CorrectionRequestFactory.php
src/database/factories/UserFactory.php
src/database/seeders/AttendanceTableSeeder.php
src/database/seeders/BreakTimeTableSeeder.php
src/database/seeders/CorrectionRequestTableSeeder.php
src/database/seeders/StatusTableSeeder.php
src/database/seeders/UserTableSeeder.php
src/database/seeders/DatabaseSeeder.php
```
## テストアカウント

|区分|名前|email|ステータス|
|---|---|---|---|
|管理者|管理者A|admin_a@gmail.com|なし|
|管理者|管理者B|admin_b@gmail.com|なし|
|一般ユーザー|山田 太郎|yamada@example.com|出勤中|
|一般ユーザー|佐藤 花|sato@example.com|休憩中|
|一般ユーザー|鈴木 一郎|suzuki@example.com|退勤済|
|一般ユーザー|田中 美咲|tanaka@example.com|出勤中|
|一般ユーザー|高橋 健太|takahashi@example.com|勤務外|
|一般ユーザー|川島 明|nakamura@example.com|出勤中|
|一般ユーザー|小林 真子|kobayashi@example.com|休憩中|

パスワード：password（テストアカウント共通）

## 使用技術（実行環境）
- PHP8.4.8
- Laravel8.83.8
- MySQL8.0.26

## ER図
![画像](https://coachtech-lms-bucket.s3.ap-northeast-1.amazonaws.com/question/20251205051037_ER_attendance.png)
## URL
- 開発環境：http://localhost/
- phpMyAdmin:：http://localhost:8080/

