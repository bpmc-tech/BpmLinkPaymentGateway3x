![LOGO](https://merchant.bpmc.jp/img/color-logo.png)


# BpmLinkPaymentGateway3x

EC-CUBE3.x用のBPMクレジット決済プラグイン

# ダウンロード
事前に下記プラグインをダウンロードの上当ガイドをお読みください。

| バージョン | ダウンロード |
|:-----------|:----------|
| 最新(v1.0.1) | [BpmLinkPaymentGateway_eccube3x_v1.0.1.zip (44KB)](https://github.com/bpmc-tech/BpmLinkPaymentGateway3x/releases) |


# はじめに

- 本決済モジュールはサイトとショップIDが１対１の場合を前提にご用意しております。
同じサイトで複数のショップIDを設定する場合は想定しておりません。
- 作業を行われる場合は、必ずEC-CUBEすべてのバックアップの取得をお願いします。
- 返金機能はついていません。返金については弊社管理システムより行ってください。
- カスタマイズに関するお問い合わせには対応しておりません。
- EC-CUBE(本体)に関するお問い合わせにつきましては、下記「開発コミュニティ」をご利用ください。
http://xoops.ec-cube.net/  
https://github.com/EC-CUBE/


## 動作環境

あらかじめ、以下URLにてご利用の環境が要件を満たしている事をご確認下さい。

http://www.ec-cube.net/product/system.php

決済モジュールとEC-CUBE本体のバージョンは以下になっています。

| 決済モジュール | EC-CUBE本体 |
|:----|:----|
| `1.0.1` | `3.0.4` `3.0.5` `3.0.6` `3.0.7` `3.0.8` `3.0.9` `3.0.10` `3.0.11` `3.0.12` `3.0.12-p1` `3.0.13` `3.0.14` |

# 決済モジュールの設定について

## 1.決済モジュール導入完了までの流れ

EC-CUBE決済モジュールの導入は下記５つの手順にて行います。

![flow.png (27.39KB)](https://merchant.bpmc.jp/document/assets/20200320/235c8fd7-c65e-469a-a9db-7adfc3a5f9ed)

## 2.決済モジュールをインストール

### インストール

- EC-CUBE管理画面にログインし、メニュー `オーナーズストア` > `プラグイン` > `プラグイン一覧`をクリックし`プラグイン一覧`のページへ遷移してください。
- 独自プラグインの`プラグインのアップロードはこちら`をクリックしてください。  
![eccube001.png (38.59KB)](https://merchant.bpmc.jp/document/assets/20200320/df8a8203-1ba6-4388-b786-933c08638139)

- プラグインのアップロードへ移動したら`ファイルを選択`ボタンをクリックし、弊社よりお送りしたファイル`BpmLinkPaymentGateway_eccube3x_vx.x.x.zip`ファイルを選択してください。  
ファイル選択ダイアログが表示されます。  
ファイル選択ができましたら`アップロード`ボタンをクリックしてください。  
![eccube002.png (15.01KB)](https://merchant.bpmc.jp/document/assets/20200320/79e9be86-638a-40e1-87d0-1aabcff6e6fb)  
プラグインページへ戻り、一覧に`BpmLinkPaymentGateway `が表示されていましたらインストールが成功です。

### 決済モジュールの有効化

アップロードした段階では、決済モジュールは<b style="color:red">停止中</b>です。  
一覧にある`有効にする`をクリックしてください。  
![eccube003.png (21.3KB)](https://merchant.bpmc.jp/document/assets/20200320/367d42cf-402b-4193-9005-8fcb6bb5b316)

`停止中`が消え、設定ボタンが表示されましたら、決済モジュールは有効になりました。  
次は決済モジュールの設定を行います。

## 3.決済モジュールの設定を行う

### (1) EC-CUBE側の設定

- `設定`ボタンをクリックしてください。

![eccube004.png (19.9KB)](https://merchant.bpmc.jp/document/assets/20200320/e3f82731-e7f8-4073-a95d-4070be657dc9)

- 設定画面に移動したら、契約した際に弊社が発行した`API TOKEN`を入力してください。
入力が完了したら`設定`ボタンをクリックしてください。  
![eccube005.png (32.96KB)](https://merchant.bpmc.jp/document/assets/20200320/913dbecc-299a-46c7-9cf9-3eb657be5072)  
<b style="color:red;">API TOKENは店舗管理システムにログイン後、`利用内容` => `システム利用内容`のページからご確認いただけます。</b>

### (2) 決済通知URLの設定

EC-CUBEが決済通知を受取るために、弊社管理システム側で決済通知URLの設定が必要です。

https://merchant.bpmc.jp/

ログイン後、メニュー `決済システム管理` > `リンク決済` > `結果通知設定(HTTP)` をクリックし、結果通知設定(HTTP)画面に遷移してください。

下記内容を設定してください。
- **送信状態**  
送信する(POST)
- **HTTP結果通知**
```
http(s)://<あなたのEC-CUBEサイトドメイン>/bpm_link_payment/fook_result
```
入力が完了したら`保存`ボタンをクリックしてください。


## 4.支払い方法設定を行う

EC-CUBE管理画面のメニュー `設定` > `基本情報設定` > `支払方法設定`をクリックし、支払方法管理ページへ遷移してください。

- 自動で、`BPMクレジットカード決済`が追加されています。
- 一覧の`...`をクリックすると操作メニューが表示されるので、`編集`をクリック
- 支払い方法について、名称や手数料はご自由に設定してください。  
利用条件は弊社と契約した際に発行された、決済可能金額を設定してください。  
弊社の管理システムからご確認いただけます。

## 5. 配送方法設定を行う
EC-CUBE管理画面のメニュー `設定` > `基本情報設定` > `配送方法設定`をクリックし、配送方法管理ページへ遷移してください。

- 対象の配送方法の`...`をクリックすると操作メニューが表示されるので、`編集`をクリック
- 支払い方法設定で`BPMクレジットカード決済`にチェックをいれて、`登録`してください。

## 5. 動作確認を行う

実際にEC-CUBEにて商品をカートに入れてレジにすすみます。  
支払い方法を`BPMクレジットカード決済`に変更し、`BPMクレジットカード決済へ`ボタンをクリックしてください。

![eccube006.png (26.2KB)](https://merchant.bpmc.jp/document/assets/20200320/ae429683-00f7-4aa5-bd28-7a8f21d28469)

弊社の決済ページへ遷移すれば正常に動作しております。

![eccube007.png (64.26KB)](https://merchant.bpmc.jp/document/assets/20200320/c7d83d6c-d5bd-4ad0-9394-c6f2d0416a96)

# 決済結果の確認

EC-CUBE管理画面にログインし、メニュー `受注管理` > `受注マスター`をクリックし`受注マスター`のページへ遷移してください。  
検索条件の支払方法を`BPMCクレジットカード決済`に設定し検索してください。  
表示された一覧の注文番号をクリックし注文の詳細ページへ移動してください。

お支払情報の項目に`決済結果` `承認番号` `決済金額` が追加されています。  
![eccube008.png (20.04KB)](https://merchant.bpmc.jp/document/assets/20200320/260dc30f-d6fa-4a6f-a507-b68c312ebc9e)

弊社管理システムの決済一覧と紐付けを行う場合は、`承認番号`を利用してください。

