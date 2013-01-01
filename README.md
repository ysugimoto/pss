# "PSS" : PHP-CSS-Preprocessor on console/web interface

Last update : 2013/01/01

PHPベースのCSSプリプロセッサです。ループや関数の実行、
Sassなどのmixin/extendなどの基本的な構文をサポートしています。

コンソールからのコンパイル、またはWebインターフェースからの動的なCSSコンパイルが可能です。

## ファイルフォーマット

「.pss」という拡張子のファイルを解析して.cssファイルに変換します。


## コンパイル

付属のシェルスクリプトから実行できます。

```pss.sh
./pss.sh input_file [output_file] [-mdl]
```

### コマンド引数

* input_file : **必須**コンパイル対象の入力ファイルです。
* output_file : 出力ファイル。指定がなければ結果は標準出力です。

### コマンドオプション

* m : 可能な限りminifyして出力します。
* d : デフォルトでは、分離して書かれた同一セレクタがあればそのプロパティをマージしますが、このオプションが渡されるとマージを行いません。開発段階では指定するといいかもしれません。
* l : セレクタ間に改行を挟む整形オプションです。

## .pssファイルの記述方法

CSSファイルの構文に加えて、いくつか拡張した構文をサポートしています。
**また、各定義分の行末にはセミコロン（;）が必須です。**

### 変数の利用（variable）

「$変数名: 値」という構文で変数が定義できます。変数の値には、int/string/array/hashが利用できます。

```
$int: 10; # 整数
$string: 100px; # 文字列
$array: [1, 2, 3, 4, 5]; # 配列
$hash: {fruit: apple, varitable: carrot}; # ハッシュ
```

構文中では、$int; などで変数が利用できます。
**なお、変数は定義した順に利用されます。変数の巻き上げは起こりませんので注意して下さい。**

### 拡張識別子（@ルール）

CSSにおける識別子に加え、PSSでは幾つかの拡張識別子をサポートしています（かつ、それらは全てプラグイン形式で実行されます）。ルールは、「定義セクション」と「実行セクション」、「インラインセクション」で解析されます。

#### 定義セクション

`@rule name[(arguments)] { ... }`という形式が定義セクションとなります。定義セクションの内容はFactoryされ（実際にはプラグイン側で書かないといけませんが）、実行セクションに置いてnameを指定して実行させることができます。以下はmixinの例です：

```
@mixin sample {
  width: 100px;
  height: 100px;
  background-color: #000;
}
```

name部分に引数フォーマットの記述があれば、定義セクション内で使えるローカル変数が定義でき、実行セクションにおいて引数を渡して実行させることができます。

```
@mixin sample($size = 100px) {
  width: $size;
  height: $size;
  background-color: #000;
}
```

#### 実行セクション

セレクタ内でnameを指定して実行させることができます。

```
.selector {
  @mixin sample;
}

/* コンパイル */

.selector {
  width: 100px;
  height: 100px;
  background-color: #000;
}
```

#### インラインセクション

プロパティ記述中などにインラインで指定して実行結果を得る事ができます。
プラグインによってはサポートしていないものもあります（定義に依存します）。
以下は、インラインで画像をdata-uri形式にエンコードする例です。

```
.selector {
  background-image: url(@base64(./image.png));
}
```


なお、定義セクションの巻き上げも行われません。つまり、定義セクションは実行セクションよりも前に存在している必要があります。ただし、定義セクション内の実行セクションは遅延解析が行われるので、実際に利用されるまでに定義されていればOKです。


### 制御構文

いわゆるプログラミング言語における制御構文を利用できます（**現在はforループのみです**）。
以下のように、`@制御名 (条件式): ~ @end制御名;`で括ることで制御構文を表現します。


```
@制御名 (条件式):

...何か定義

@end制御名;
```

#### forループ

指定回数だけループして制御を行うことができます。

```
@for ($i in 4px):
.selector {
  width : $i;
}
@endfor;

/* コンパイル */
.selector {
  width : 0px;
}
.selector {
  width : 1px;
}
.selector {
  width : 2px;
}
.selector {
  width : 3px;
}
```

また、カウンタのインクリメントもatを加えることで指定できます。

```
@for ($i in 4px at 2):
.selector {
  width : $i;
}
@endfor;

/* コンパイル */
.selector {
  width : 0px;
}
.selector {
  width : 2px;
}
```

ループ対象のデータは変数を指定することもできますし、即時で作成することもできます。
**ループ対象のデータは、その形式によってループ変数が変わるので注意して下さい**

* int型の場合 : そのまま数値が渡されます。
* string型の場合 : 数値として表現可能な場合（ex. "100px"）、その数値と単位表現が渡されます。
* array型の場合 : それぞれの配列のインデックスに対応する値が渡されます。


```
$value: 100px;

/* 変数を使う例 */
@for ($i in $value at 10):
.selector {
  width : $i;
}
@endfor;

/* 即時変数を使う例
@for ($i in [1, 2, 3]):
.selector {
  width : $i;
}
@endfor;
```

### if文

現在未実装です。


## PHP組み込み関数の利用

構文中でバックティック演算子（`）で括られたものはPHPの組込み関数の評価結果に置換することができます。
関数名、引数リストを半角スペースで区切ることで

```
.selector {
  width: `rand 0 100`px;
}
```

この記述は、PHPにおける`rand(0, 100);`と等価であり、実行結果に置換されます。


## 現在サポートしているプラグイン

現時点で実装されているプラグインモジュールのリストです。
利用できるフェーズはそれぞれ（def:定義セクション、exec:実行セクション、inline:インラインセクション）です。
フェーズの記述のあるものが利用できます。

* [mixin（def/exec）](#mixin)
* [extend（exec）](#extend)
* [prefix（exec）](#prefix)
* [include（exec/inline）](#include)
* [base64（inline）](#base64)
* [calc（inline）](#calc)


### <a name="mixin">Mixin

特定のルール形式を定義セクションでまとめておき、セレクタ内で呼び出すことでスタイルの再利用ができます。

```
/*　定義セクション */
@mixin sample($def = 5px) {
  border: solid $def #000000;
}

... 

/* 実行セクション */
.selector {
 @mixin sample(10px);
}
```

### <a name="extend">Extend

セレクタのルールを継承して埋め込むことができます。基底スタイルからの派生パターンを使うのに便利です。

```
.selector {
  width : 100px;
  height: 100px;
  font-size: 3em;
  color : #333333;
}

... 

/* .selectorを継承 */
.selector2 {
 @extend .selector;
 color: #666666;
 font-size: 4em;
}

------

/* コンパイル */
.selector {
  width : 100px;
  height: 100px;
  font-size: 3em;
  color : #333333;
}
.selector2 {
  width : 100px;
  height: 100px;
  font-size: 4em;
  color : #666666;
}
```

### <a name="prefix">Prefix

ベンダープレフィックスを一括して出力できます。

```
.selector {
  @prefix border-radius(5px);
}

/* コンパイル */
.selector {
  -webkit-border-radius: 5px;
  -moz-border-radius: 5px;
  -ms-border-radius: 5px;
  -o-border-radius: 5px;
  border-radius: 5px;
}
```

第二引数で付与するプレフィックスを制限することもできます（指定がなければ全て出力されます）

```
.selector {
  @prefix border-radius(5px, wm);
}

/* コンパイル */
.selector {
  -webkit-border-radius: 5px;
  -moz-border-radius: 5px;
  border-radius: 5px;
}
```

アルファベットとプレフィックスの対応は以下の通りです：

* w : -webkit-
* m : -moz-
* i : -ms-
* o : -o-


### <a name="include">Include

分離した外部.pssファイルを読み込んで解析対象とすることができます。
パスは、解析中のファイルパスからの相対パスとなります。

```
@include ./partial.pss
```

なお、メインのpssファイルで定義している変数を利用することもできますが、
読み込みを行うよりも前に定義しておく必要があるので注意が必要です。
一般的には、mixinのリストなどを分離してロードするような使い方が多いと思います。

### <a name="base64">Base64

画像ファイルなどのバイナリデータをdata-uriの形式にエンコードして出力します。

```
div#bg {
  background-image: url(@base64(./image.png));
}

/* コンパイル */
div#bg {
  background-image: url(data:image/png;base64,[encoded string...]);
}
```

MIMEタイプはファイルの拡張子から選択されますので、拡張子の無いファイルは例外が発生します。


### <a name="calc">Calc

四則演算を行うインライン関数です。値には変数や数値を使うことができますが、現時点ではすべて計算結果はpxとして出力されるため、%の演算は行わないようにしてください。

```
.selector {
  width : @calc(100px * 5 / 10);
}

/* コンパイル */
.selector {
  width : 50px;
}
```

その他、便利そうなプラグインを考え中です...

TODO: プラグインの書き方について















