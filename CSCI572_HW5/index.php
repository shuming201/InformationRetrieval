<?php
ini_set('memory_limit','4084M');
include 'SpellCorrector.php';
include 'simple_html_dom.php';
header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$path ="/home/shuming/solr-7.7.0/Reuters/reutersnews/reutersnews/";

//Read the mapping file
$file = fopen('/home/shuming/solr-7.7.0/Reuters/URLtoHTML_reuters_news.csv', 'r');
$csv = array();
// Read csv file
while (($line = fgetcsv($file)) !== FALSE) {
    $csv[$line[0]] = $line[1];
}
fclose($file);

$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false; 
$results = false;

// use desending order for pagerank
if($query){
$additionalParameters = array(
 'sort' => 'pageRankFile desc'
);

require_once('Apache/Solr/Service.php');
// create a new solr service instance - host, port, and corename
// path (all defaults in this example)
$solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample/');
 if( ! $solr->ping()) { 
            echo 'Solr service is not available'; 
        } 
     else{
     
     }

try
{
$queryterms = explode(" ",$query);
$original_query = $query;
$query = "";
$flag = 0;
$fg =isset($_REQUEST['f']) ? true : false;
if($fg == false){
foreach($queryterms as $term){
    $t = SpellCorrector::correct($term);
$t = SpellCorrector::correct($term);

    if(trim(strtolower($t)) != trim(strtolower($term))){
        $flag = 1;
    }
    $query = $query." ".$t;
}

$query = trim($query);
}
else{
    $query = $original_query;
}

//return different search algorithms
if (isset($_GET["optn"]) && $_GET["optn"]=="rank"){
    $results = $solr->search($query, 0, $limit,$additionalParameters);
}
else{
    $results = $solr->search($query, 0, $limit);
}


}
catch (Exception $e)
{
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
} 
}
?>
<!DOCTYPE HTML>
<html>
  <head>
    <title>Shuming Hao HW5</title>
    <script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <style type="text/css">
      #title {
        font-size: 18px;
      }
      #url {
        font-size: 14px;
      }
      #description {
        font-size: 14px;
      }
      #id {
        font-size: 14px;
        color:grey;
      }
    </style>
  </head>
<script>
    $(function() {
        var URL_PREFIX = "http://localhost:8983/solr/myexample/suggest?indent=on&q=";
        var URL_SUFFIX = "&wt=json";
        $("#q").autocomplete({
      source : function(request,response) {
        var input=$("#q").val().toLowerCase().split(" ").pop(-1);
        var URL=URL_PREFIX+input+URL_SUFFIX;
        $.ajax({
          url : URL,
          success : function(data) {
            var input=$("#q").val().toLowerCase().split(" ").pop(-1);
            var suggestions=data.suggest.suggest[input].suggestions;
            suggestions=$.map(suggestions,function(value,index){
              var prefix="";
              var query=$("#q").val();
              var queries=query.split(" ");
              if(queries.length>1) {
                var lastIndex=query.lastIndexOf(" ");
                prefix=query.substring(0,lastIndex+1).toLowerCase();
              }
              if (prefix == "" && is_stop_word(value.term)) {
                return null;
              }
               if(!/^[0-9a-zA-Z]+$/.test(value.term)) {
                return null;
              }
              return prefix+value.term;
            });
            response(suggestions.slice(0,5));
          },
          dataType: 'jsonp',
          jsonp: 'json.wrf'
        });  
      },
      minLength: 1 
    });
    });

function is_stop_word(stopword) {
  var regex=new RegExp("\\b"+stopword+"\\b","i");
  return stopWords.search(regex) < 0 ? false : true;
 }

</script>

<div id = "search" top = "50px" left = "50px">
<form accept-charset="utf-8" method="get" >
<label class="col-form-label" for="q">Search:</label>
<input id="q" name="q" type="text" value="<?php echo htmlspecialchars($original_query, ENT_QUOTES, 'utf-8'); ?>"/>
</br>
</br>
<input type="radio" name="optn" checked <?php if (isset($_GET["optn"]) && $_GET["optn"]=="default") echo "checked"?> value ="default"> LUCENE
 <input type="radio" name="optn" <?php if (isset($_GET["optn"]) && $_GET["optn"]=="rank") echo "checked" ?> value="rank"> PAGERANK
</br>
</br>
<input type="submit"/> 
</br>
</br>

</form>

</div>
<div style="text-align:left">
<?php



// display results
if ($results) {
    //flag == 1 means there is spelling error
if($flag == 1){ ?>
<p>Showing results for spell corrected term: <a href="http://localhost/index.php?rank=<?php echo $_REQUEST['optn']; ?>&f=true&q=<?php echo $query; ?>"><?php echo $query;?></a> </p>
<p>Do you want to search instead for the misspelled term: <a href="http://localhost/index.php?rank=<?php echo $_REQUEST['optn']; ?>&f=true&q=<?php echo $original_query; ?>"><?php echo $original_query;?></a> </p>
<?php }
$total = (int) $results->response->numFound; 
$start = min(1, $total);
$end = min($limit, $total);
}
?>
<?php

if ($results) {
    $total = (int) $results->response->numFound;
    $start = min(1, $total);
    $end = min($limit, $total);
    echo "<div>Results {$start} - {$end} of {$total}:</div>";
}
?>
    
<ol> 
<?php
// iterate result documents

foreach ($results->response->docs as $doc)
{ 
// iterate document fields / values
echo "<li>";


$title = "";
$url = "";
$id = "";
$descp = "";

foreach ($doc as $field => $value)
{ 

if($field == "id"){
$local_file = $value;
$id = htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');
$id = str_replace($path, "", $id);
}

if($field == "title"){
$title = htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');
}

if($field == "description"){
$descp = htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');
}
}
//find url of a file
if($id != ""){
    if (isset($doc->og_url)) {
        $url = $doc->og_url;
    }
    else {
        $url = $csv[$id];
    }
}
$id = $doc->id;
echo "<a  target= '_blank'  href='{$url}'><b>".$title."</b></a></br>";
echo "<a  target= '_blank' href='{$url}'>".$url."</a></td></br>";
echo "/home/shuming/solr-7.7.0/Reuters/reutersnews/reutersnews/";
echo $id."</a></td></br>";
//echo "<a  target= '_blank' href='{$url}'>".$id."</a></td></br>";

//generate snippet, both single term query and multi term query
$snip = "";

$queryterms = explode(" ", $query);
$count = 0;
$max = sizeof($queryterms);
$prev_max = 0;
$file_content = file_get_contents($id);
$html = str_get_html($file_content);
$content =  strtolower($html->plaintext);
foreach(preg_split("/((\r?\n)|(\r\n?))/", $content) as $line)
{
      $sent = strtolower($line);
      for($i = 0 ; $i < sizeof($queryterms); $i++)
      {
          $query_term_lower = strtolower($queryterms[i]);
          if(strpos($sent, $query_term_lower) == 0)
          {
              $count = $count+1;
          }
      }
      if($max==$count)
        {
            $snip = $sent;
              break;
        }
        else if($count > 0)
        {
            $snip = $sent;
            break;
        }
        $count = 0;
    
  }
  if($snip == "")
  $snip = $desc;
  $pos_term = 0;
  $start_pos = 0;
  $end_pos = 0;
for($i = 0 ; $i < sizeof($queryterms); $i++)
  {
  if (strpos(strtolower($snip), strtolower($queryterms[$i])) !== false) 
    {
      $pos_term = strpos(strtolower($snip), strtolower($queryterms[$i]));
      break;
    }
}
if($pos_term > 80)
{
    $start_pos = $pos_term - 80; 
}
$end_pos = $start_pos + 160;
if(strlen($snip) < $end_pos)
{
    $end_pos = strlen($snip) - 1;
    $trim_end = "";
}
else
{
    $trim_end = "...";
}
if(strlen($snip) > 160)
{
    if($start > 0)
        $trim_beg = "...";
    else
        $trim_beg = "";
    $snip = $trim_beg.substr($snip , $start_pos , $end_pos - $start_pos + 1).$trim_end;
}
echo "Snippet : ";
$ary = explode(" ",$snip);
$fullflag = 0;
$snipper = "";
foreach ($ary as $word)
{
    $flag = 0;
    for($i = 0 ; $i < sizeof($queryterms); $i++)
    {
        if(stripos($word,$queryterms[$i])!=false)
        {
            $flag = 1;
            $fullflag = 1;
            break;
        }
    }
    if($flag == 1)
        $snipper =  $snipper.'<b>'.$word.'</b>';
    else
        $snipper =  $snipper.$word;	
    $snipper =  $snipper." ";	
}
//highlight query terms in snippet

$querywords1 = preg_split('/\s+/', $query);
foreach($querywords1 as $item)
$snipper = str_ireplace($item, "<strong>".$item."</strong>",$snipper);
echo $snipper."</br></br>";

}    
    

echo "</li>";


?>
</ol>

</div>
<script>
var stopWords = "a,able,about,above,across,after,all,almost,also,am,among,can,an,and,any,are,as,at,be,because,been,but,by,cannot,could,dear,did,do,does,either,else,ever,every,for,from,get,got,had,has,have,he,her,hers,him,his,how,however,i,if,in,into,is,it,its,just,least,let,like,likely,may,me,might,most,must,my,neither,no,nor,not,of,off,often,on,only,or,other,our,own,rather,said,say,says,she,should,since,so,some,than,that,the,their,them,then,there,these,they,this,tis,to,too,twas,us,wants,was,we,were,what,when,where,which,while,who,whom,why,will,with,would,yet,you,your,not";
</script>
</body> </html>
