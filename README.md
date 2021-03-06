# "PSS" : PHP-CSS-Preprocessor on console/web interface

Last update : 2013/01/06

PHPベースのCSSプリプロセッサです。ループや関数の実行、
Sassなどのmixin/extendなどの基本的な構文をサポートしています。

コンソールからのコンパイル、またはWebインターフェースからの動的なCSSコンパイルが可能です。

## デモ

オンラインツールを作りました。以下で紹介している構文などをひと通り試すことができます。
<http://pss.wnotes.net/>

## 特徴など

Sassの構文と少し違いはありますが、全ての拡張構文は「コントロール/プラグイン」という形式で実行されるのが特徴です。
つまり、mixinやextendはメタ言語組み込みの機能ではなくプラグインであり、for文やif文もコントロールによって提供される機能です。
プラグインを独自に追加/拡張することで、挙動は好きなように変更したり、自由にコントロールやプラグインを増やすことができます。



### コントロール

構文を提供する機能です。ファイルは`controls/`以下に配置されています。



### プラグイン

独自に@xxxのルールで制御を行う機能です。ファイルは`plugins/`以下に配置されています。

プラグインやコントロールの追加方法もそのうち書ければ…。



## サポートバージョン

PHP5.3以上の環境が必要です。それ以下のバージョンでも動かせるようにできますが、ちょっと面倒くさかったので…。



## ライセンス

MITライセンスです。



## ファイルフォーマット

「.pss」という拡張子のファイルを解析して.cssファイルに変換します。



## コンパイル

付属のコマンドスクリプトから実行できます。（/usr/bin/envが利用できる環境が必要です）
/usr/local/binなどにシンボリックリンクを貼って使うとコマンドっぽくなります。

```
./bin/pss input_file [output_file] [-mdlv] [--extraargs]
```

/usr/bin/envがない場合は、pssスクリプトファイルのhashbangをphpコマンドのパスに書き換えて利用して下さい。

### コマンド引数

* input_file : **必須**コンパイル対象の入力ファイルです。
* output_file : 出力ファイル。指定がなければ結果は標準出力です。

その他、--key=value などの追加オプションを渡すことで、プロセス中で変数として利用することもできます。

```
./bin/pss input_file --PATH=/path/to

/* pssファイル内で$PATHという変数が利用可能になる */
.selector {
  background-image: url($PATH);
}
```

環境のスイッチに便利ですが、コマンド変数に依存している場合、引数を渡さないとコンパイルエラーになるので注意が必要です。


### コマンドオプション

* m : 可能な限りminifyして出力します。
* d : デフォルトでは、分離して書かれた同一セレクタがあればそのプロパティをマージしますが、このオプションが渡されるとマージを行いません。開発段階では指定するといいかもしれません。
* l : セレクタ間に改行を挟む整形オプションです。
* v : コンパイル情報を標準出力します。コマンドからの実行の際にのみ利用して下さい。



## .pssファイルの記述方法

CSSファイルの構文に加えて、いくつか拡張した構文をサポートしています。
**また、各定義分の行末にはセミコロン（;）が必須です。**

### 変数の利用（variable）

「$変数名: 値」または「$変数名 = 値」という構文で変数が定義できます。変数の値には、int/string/array/boolean/nullが利用できます。
また、変数名を大文字で定義するとその値は定数扱いとなり、再代入が不可能（immutable）な値となります。
なお、値はシングルクオート、ダブルクオートで括ってもOKです。

**「$変数名: 値」はCSSプロパティの形式に似ていて紛らわしいので、「$変数名 = 値」の形式にする予定です。一応現在は両方サポートしていますが、いずれは未サポートにすると思うのでご注意下さい。**

```
$int: 10; # 整数
$string: 100px; # 文字列
$array: [1, 2, 3, 4, 5]; # 配列
$bool: true; # boolean
$null: null; # null
$CONST: "constant";

/* 再代入 */
$int: 100; # $intは100になる
$CONST: "new constant"; # エラーとなる

/* 定義済みの配列の値を変更する/値を追加する */
$array[2]: 10; # $arrayは[1, 2, 10, 4, 5]になる
$array[]: 10; # $arrayは[1, 2, 3, 4, 5, 10]になる
```

構文中では、$int; などで変数が利用できます。また、プロパティ中などのインラインで利用する場合は、<$int>として、<>で変数のセクションを明示することで展開されます。なお、配列やハッシュは出力系での置換はできません。配列の値の変更/追加は定義済みのものに限ります。未定義の配列に対して行うとエラーとなります。

**また、変数は定義した順に利用されます。変数の巻き上げは起こりませんので注意して下さい。**

変数はコンパイル結果に含まれません。スクリプト中で変数の状態を確認したい場合は、後述する@dumpプラグインを使ってコンパイル結果に含めるようにしながら確認できます。

### セレクタのネスト

Sassなどと同じくセレクタのネスト、及び親参照セレクタも実装しています。

```
.section {
  width: 500px;
  
  p {
    color: #FFFFFF;
    
    a {
      text-decoration: none;
      
      &:hover {
        text-decoration: underline;
      }
    }
  }
}

/* コンパイル */
.section {
  width: 500px;
}
.section p {
  color: #FFF;
}
.section p a {
  text-decoration: none;
}
.section p a:hover {
  text-decoration: underline;
}
```

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
**注意：定義セクションはCSSプロパテイの制御などを用途としています。そのため、定義セクション内では.selector {}などのセレクタの記述は行えません。**

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

プロパティの値として実行セクションを指定することもできますが、後述するfunctionの場合を除き、実行セクションは単体で一行に記述する方が安全です。


#### インラインセクション

プロパティ記述中などにインラインで指定して実行結果を得る事ができます。
プラグインによってはサポートしていないものもあります（定義に依存します）。
以下は、インラインで画像をdata-uri形式にエンコードする例です。

```
.selector {
  background-image: url(@base64(./image.png));
}
```


なお、定義セクションの巻き上げも行われません。つまり、定義セクションは実行セクションよりも前に存在している必要があります。ただし、定義セクション内の実行セクション、及び変数とインラインセクションは実行時に遅延解析が行われるので、実際に利用されるまでに定義されていればOKです。



### 制御構文

いわゆるプログラミング言語における制御構文を利用できます。サポートしている制御構文は、「if」、「for」、「switch」です。
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
**ループ対象のデータは、その形式によってループ変数が変わるので注意して下さい。**

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

プログラミング言語におけるif-elseif-elseの条件分岐を表現できます。現状、()でネストしないシンプルな条件式のみ評価できます。
比較演算子は **>、<、<=、>=、==、!=、===、!==** が使えます。
論理演算子は **&&またはand、||またはor** が使えます。できるだけ条件式は一行にまとめて下さい。
また、if文のネストには対応していません。ネストするとシンタックスエラーとなるので注意して下さい。

#### ifのみの例

```
$int: 10;

@if ( $int == 10 ):
.selector {
  width: 100px;
}
@endif;
```

#### if-elseの例

```
$int: 10;

@if ( $int > 10 ):
.selector {
  width: 100px;
}
@else:
.selector {
  width: 50px;
}
@endif;
```

#### if-elseif-elseの例

```
$int: 10;

@if ( $int > 10 ):
.selector {
  width: 100px;
}
@elseif ( $int == 10 ):
.selector {
  width: 150px;
}
@else:
.selector {
  width: 50px;
}
@endif;
```

今後はインラインセクションの評価結果を条件評価に使えるようにしようかと思っています。

### switch文

switch文もサポートしています。それぞれswitch、case、break、defaultのキーワードに@をつけて表現します。

```
$v: 10;

@switch ( $v ):
  @case 1:
    .section {
      margin-left: 1px;
    }
    @break;
  @case 10:
    .section {
      margin-left: 10px;
    }
    @break;
  @default:
    @break;
@endswitch;
```

上記の場合、2番目のcase文が実行されます。
また、プログラム言語におけるswitch文と同様、case文の中に@break;の記述がない場合はフォールスルーが起こります。



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

* [function（def/exec）](#function)
* [mixin（def/exec）](#mixin)
* [extend（exec）](#extend)
* [prefix（exec）](#prefix)
* [include（exec/inline）](#include)
* [alias（def）](#alias)
* [base64（inline）](#base64)
* [calc（inline）](#calc)
* [pow（inline）](#pow)
* [concat（inline）](#concat)
* [dump（exec）](#dump)

---

### <a name="function">Function

特定の処理を行うようなマクロ関数を定義し、PSSファイル中で実行してその結果を利用することができます。
定義内ではforやifなどの制御構文を使うこともできます。

結果の返却には@return識別子を指定します。ただし、@return文にはプリミティブな値または変数のみが返却できます。
よって、処理の結果を戻すような指定はできません。

```
@function test($x = 10, $y = 20):
  @if ($x == 10):
    $v: $x;
  @elseif ($y = 20):
    $v: $y;
  @else:
    $v: 0;
  @endif;
  @return $v; # OK
  @return @calc($v * 100); # NG
@endfunction

/* 実行 */
.selector {
  width : @fucntion test(10)px;
}

/* コンパイル */
.selector {
  width : 10px;
}
```

現状、意識的に使うケースは少ないかもしれないので、もう少し使い勝手を良くしたいと思います。

---

### <a name="mixin">Mixin

特定のルール形式を定義セクションでまとめておき、セレクタ内で呼び出すことでスタイルの再利用ができます。

```
/*　定義セクション */
@mixin sample($def = 5px) {
  border: solid <$def> #000000;
}

... 

/* 実行セクション */
.selector {
 @mixin sample(10px);
}
```

---

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

---

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

---

### <a name="include">Include

分離した外部.pssファイルを読み込んで解析対象とすることができます。
パスは、解析中のファイルパスからの相対パスとなります。

```
@include ./partial.pss;
```

なお、メインのpssファイルで定義している変数を利用することもできますが、
読み込みを行うよりも前に定義しておく必要があるので注意が必要です。
一般的には、mixinのリストなどを分離してロードするような使い方が多いと思います。

---

### <a name="alias">Alias

定型フォーマットにラベルを付けてエイリアスを作成することができます。

```
@alias name:value;
```

valueの値には文字列のみが指定できます。変数の値は使えません。
作成後、エイリアスの呼び出しは「&エイリアス名」、またはインラインで「<&エイリアス名>」で展開します。

```
@alias file:input[type=file];

.selector &file {
  display: block;
}

/* コンパイル */
.selector input[type=file] {
  display: block;
}
```

変数とほぼ同じ機能ですが、変数は主にプロパティの値への用途を目的としていて、
エイリアスは主にセレクタ部分の作成の簡易化を目的としています。
（セレクタ部分の変数は展開されません。これは、CSS Selectors Level 4において、$を用いるセレクタが存在するからです）

なお、エイリアスは単文のみで、複数行に渡る定義セクションなどには使えません。

---

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

---

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

---

### <a name="pow">Pow

指数計算を行います。

```
$int: 100;

.section {
  width: @pow($int, 2)px;
}

/* コンパイル */
.section {
  width: 10000px;
}
```

2つの引数が必須です。

---

### <a name="concat">Concat

シンプルな文字列の結合を行います。引数全てがintまたはstringでないといけません。

```
$w: 50;

.section {
  width: @concat($w, px);
}

/* コンパイル */
.section {
  width: 50px;
}
```

なお、この場合は`width: <$w>px`と変数展開を行えば同じ結果となります。

---
### <a name="dump">Dump

pssファイル中に宣言された変数/エイリアスをDumpして確認することができます。
Dumpする変数/エイリアスはカンマ区切りで複数指定することができます。

```
$str: this is test;
$int: 10;

@dump $str, $int;

/* コンパイル */
...

/* Pss Dump info ========================
Dump1: this is test
Dump1: 10
======================== Pss Dump info */
```

コンパイル後の出力結果の末尾にコメントセクションとして情報が出力されます。
@dumpを実行した時点での変数の値を確認できます。

---

その他、便利そうなプラグインを考え中です...

TODO: プラグインの書き方について















