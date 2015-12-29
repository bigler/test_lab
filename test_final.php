<?php

/* 
 * Тестовое задание для UNITECSYS.COM
 * @author Dmitry Gurbatov (ok.sulde@gmail.com)
 * @copyright 2015
 */

/* @var array $test
 *   Тестовый массив с промежуточными точками
 *   
 *   ВАЖНОЕ ДОПОЛНЕНИЕ №1:  В тестовом задании не указано напрямую равнозначны ли маршруты: А->Б и Б->A,
 *                          поэтому примем за основу, что стоимость из точки А в точку Б равна стоимости из точки Б в точку А 
 *                          В противном случае алгоритм заполнения матрицы вариантов был бы реализован по другому.
 * 
 *   ВАЖНОЕ ДОПОЛНЕНИЕ №2:  Предполагается, что точки отправления и прибытия присутствуют в принимаемом функцией getRoute() массиве $arr.
 *                          В противном случае следует проверять корректность ввода данных и возвращать в функции код ошибки. 
 * 
 *   ВАЖНОЕ ДОПОЛНЕНИЕ №3:  Можно было бы запихнуть в функцию findAllRoutes сразу и алгоритм поиска оптимального маршрута (но для этого
 *                          пришлось бы связывать с ней массив $matrix и переменную $min_price. Пожертвовал размером кода в угоду 
 *                          читаемости алгоритма.  Учитывая, что данное решение тестовое, здесь нет контроля корректности заполнения
 *                          массива промежуточных точек и введенных населенных пунктов начала и конца маршрута.
 *                          P.S.  Я бы предпочел реализовать этот алгоритм в виде класса, но условие тестового задания требует
 *                          написать Функцию.   
 *  
 */
$test=array(
    array(
        'from'=>'Astana',
        'to'=>'Moscow',
        'price'=>100
    ),
    array(
        'from'=>'Astana',
        'to'=>'Cordoba',
        'price'=>300
    ),
    array(
        'from'=>'Astana',
        'to'=>'Dusseldorf',
        'price'=>250
    ),
    array(
        'from'=>'Astana',
        'to'=>'Edinburgh',
        'price'=>50
    ),
    array(
        'from'=>'Moscow',
        'to'=>'Cordoba',
        'price'=>600
    ),
    array(
        'from'=>'Moscow',
        'to'=>'Dusseldorf',
        'price'=>40
    ),
    array(
        'from'=>'Moscow',
        'to'=>'Edinburgh',
        'price'=>80
    ),
    array(
        'from'=>'Cordoba',
        'to'=>'Dusseldorf',
        'price'=>100
    ),  
    array(
        'from'=>'Cordoba',
        'to'=>'Edinburgh',
        'price'=>870
    ),  
    array(
        'from'=>'Dusseldorf',
        'to'=>'Edinburgh',
        'price'=>100
    ),  

);
/*
 * Функция getRoute()
 * @param string $city_from Точка отправления
 * @param string $city_to Точка прибытия
 * @param array $arr Список промежуточных пунктов в формате array('from'=>'A','to'=>'B','price'=>100)
 * @return array $ret Возвращаемый результат в формате array('best_route'=>array('A','B'...'Z'),'min_price'=>100)
 * 
 */
function getRoute($city_from,$city_to,$arr) {
    $matrix=array();
    $best_route=array();

    // make full matrix (A->B = B->A = the same price )
    /* создаем матрицу возможных вариантов стоимости 
     * Пример
     *      | A | B | C | D |
     *   A  | * | 5 | 7 | 3 |
     *   B  | 5 | * | 8 | 1 |
     *   C  | 7 | 8 | * | 9 | 
     *   D  | 3 | 1 | 9 | * | 
     * 
     */   
    foreach ($arr as $route) {
        $from=$route['from'];
        $to=$route['to'];
        $price=$route['price'];
        $matrix[$from][$to]=$price;
        $matrix[$to][$from]=$price;

    } 
    // Присваиваем min_price маршруту из точки отправления до точки прибытия
    $min_price=$matrix[$city_from][$city_to];
    $points = array_keys($matrix[$city_from]);
    
    // @var array $all_routes  Коллекция всех возможных маршрутов из точки $city_from до точки $city_to
    $all_routes=array();
   // заполним все маршруты
    findAllRoutes($city_from,$city_to,$points,$all_routes);
    
    // Теперь посчитаем минимальную стоимость по каждому маршруту и выберем оптимальный, присвоив его массиву $best_route и сохранив значаение минимальной суммы в переменную $min_price
    foreach ($all_routes as $route) {
        $calc_route=explode('->',$route);
        $prev=$city_from; $calc_price=0;
        foreach ($calc_route as $next) {
             if ($prev==$next) continue;
             $calc_price+=$matrix[$prev][$next];
             $prev=$next;
        }
        if ($calc_price<=$min_price) {
             $min_price=$calc_price;
             $best_route=$calc_route;
         }
    }
    // сформируем возвращаемый результат
    $ret=array();
    $ret['best_route']=$best_route;
    $ret['total_price']=$min_price;
    return $ret;
}
/*
 * Функция findAllRoutes()
 * Рукурсивная функция - формирующая все возможные маршруты движения из точки $from  в точку $to
 * Используется как вспомогательная функция для getRoute()  
 * @param string $from Точка отправления
 * @param string $to Точка прибытия
 * @param array $arr Список промежуточных пунктов в формате array('A','B','C') Фактически список всех населенных пунктов, за исключением  пункта отправления
 * @return array $ret Возвращаемый результат в формате array(0=>string('A->B')...n=>('A->D->E'))
 * 
 */
function findAllRoutes($from,$to,$arr, &$collect) {
    for ($i=0; $i<sizeof($arr);$i++) {
        $arrcopy = $arr;
      
        if ($arr[$i]==$to) {
            $collect[]=$from.'->'.$to;
            continue;
        }
        $elem = array_splice($arrcopy, $i, 1); // removes and returns the i'th element
        if (sizeof($arrcopy) > 0){          
            findAllRoutes($from."->".$elem[0],$to,$arrcopy, $collect);
           
        }  
    }   
}

/*
 *  DEMO-function : show cool result of getRoute()
 *  Функция для отображения результата вычислений
 *  @param array $arr - array('best_route'=>array('A','B'...'Z'),'min_price'=>100)
 *  @return echo result
 */
function showResult($arr) {
    $route=implode(' -> ',$arr['best_route']);
    echo "THE BEST ROUTE IS: ".$route."  WITH TOTAL PRICE - ".$arr['total_price']."$<br>";
}

//   *********************** START **************************
/*
 *   EXAMPLES with getRoute() 
 *   Cordoba -> Edinburgh must be 200
 *   Edinburgh -> Cordoba must be 200 
 *   Dusseldorf -> Moscow must be 40
 *   Astana -> Cordoba must be  240
 *   
 */

echo 'Cordoba  -> Edinburgh'."<br>";
showResult(getRoute('Cordoba','Edinburgh',$test));
echo '-----------------------------------------------------------------------'."<br>";

echo 'Edinburgh  -> Cordoba'."<br>";
showResult(getRoute('Edinburgh','Cordoba',$test));
echo '-----------------------------------------------------------------------'."<br>";

echo 'Cordoba  -> Astana'."<br>";
showResult(getRoute('Cordoba','Astana',$test));
echo '-----------------------------------------------------------------------'."<br>";

echo 'Astana  -> Cordoba'."<br>";
showResult(getRoute('Astana','Cordoba',$test));
echo '-----------------------------------------------------------------------'."<br>";

echo 'Moscow  -> Astana'."<br>";
showResult(getRoute('Moscow','Astana',$test));
echo '-----------------------------------------------------------------------'."<br>";

echo 'Astana  -> Moscow'."<br>";
showResult(getRoute('Astana','Moscow',$test));
echo '-----------------------------------------------------------------------'."<br>";

echo 'Edinburgh  -> Moscow'."<br>";
showResult(getRoute('Edinburgh','Moscow',$test));
echo '-----------------------------------------------------------------------'."<br>";

echo 'Cordoba  -> Moscow'."<br>";
showResult(getRoute('Cordoba','Moscow',$test));
echo '-----------------------------------------------------------------------'."<br>";

echo 'Astana  -> Dusseldorf'."<br>";
showResult(getRoute('Astana','Dusseldorf',$test));
echo '-----------------------------------------------------------------------'."<br>";

echo 'Dusseldorf -> Edinburgh'."<br>";
showResult(getRoute('Dusseldorf','Edinburgh',$test));
echo '-----------------------------------------------------------------------'."<br>";

?>