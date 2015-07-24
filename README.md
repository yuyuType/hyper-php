hyper-php
===============

標準のPHPに足りない機能を足し、効率のよいプログラミングを支援するためのライブラリ

Option型

~~~php
use Hyper\Data\Option;

$str = Option::Some('Hello World')->map('strtoupper')->getOrElse("");
echo $str; // "HELLO WORLD"

$str = Option::None()->map('strtoupper')->getOrElse("");
echo $str; // ""
~~~

Either型

~~~php
use Hyper\Data\Either;

$str = Either::Right('Hello World')->map('strtoupper')->getOrElse("");
echo $str; // "HELLO WORLD"

$str = Either::Left('Hello World')->map('strtoupper')->getOrElse('Hyper\Func::identity');
echo $str; // "Hello World"
~~~
