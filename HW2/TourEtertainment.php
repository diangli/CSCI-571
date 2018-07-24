<?php
	if(isset($_GET['keyword'])){	
		$fromwhere=$_GET['fromchoice'];
		if($fromwhere=="Here"){		
			//$location="34.0266,-118.2831";   //here
			$location=$_GET['lat'].",".$_GET['lng'];
		}
		else{
			$locationinput=$_GET['location'];    //location
			$addressforapi=str_replace(" ","+",$locationinput);
			$geocodingapi="https://maps.googleapis.com/maps/api/geocode/json?"."address=".$addressforapi."&key=AIzaSyDBMobuO8lyXMgictZsZf0dOd2kH7-Tpxs";
			$fileContent_geoencoding = file_get_contents($geocodingapi) or die("try again");
			//file_put_contents("address.json", $fileContent_geoencoding);
			
			$geojsonObj = json_decode($fileContent_geoencoding,true);
			
			if($geojsonObj['status']!="OK"){
				$geojsonObj['found']="no";
				echo json_encode($geojsonObj);
				return;
			}
			$startlat=$geojsonObj['results'][0]['geometry']['location']['lat'];
			$startlng=$geojsonObj['results'][0]['geometry']['location']['lng'];
			$location=$geojsonObj['results'][0]['geometry']['location']['lat'].",".$geojsonObj['results'][0]['geometry']['location']['lng'];
		}

		$keyword=$_GET['keyword'];	
		$distance=$_GET['distance'];
			if($distance==0){$radius=16090;}
			else{$radius=$distance*1609;}
		$category=str_replace(" ","_",$_GET['category']);
		
		$nearbyapi="https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=".$location."&radius=".$radius."&type=".$category."&keyword=".$keyword."&key=AIzaSyDpwobkPxkUkpKZdboGYuqdlwjoMK-s2-o";
		$fileContent_nearby = file_get_contents($nearbyapi) or die("try again");
		if($fromwhere=="Here"){
			echo $fileContent_nearby;
			return;
		}
		else{  //from location
			$nearbyjsonObj = json_decode($fileContent_nearby,true);	
			$nearbyjsonObj['mylat']=$startlat;
			$nearbyjsonObj['mylng']=$startlng;
			echo json_encode($nearbyjsonObj);
			return;		
		}	
	}

	if(isset($_GET[''])){
		$placeid=$_GET['placeid'];
		//echo $placeid;		
		$placesapi="https://maps.googleapis.com/maps/api/place/details/json?placeid=".$placeid."&key=AIzaSyCRMA5RIe3UEpmS7IiZp1GqSYPUXmURgFM";
		$fileContent_places=file_get_contents($placesapi) or die("try again");

		if(isset($_GET['review'])){
		echo $fileContent_places;
		return;}

		if(isset($_GET['photo'])){
		echo $fileContent_places;
		return;}
				
		$allphotos=json_decode($fileContent_places,true);

		if(count($allphotos['result']['photos'])>=5){
			for($m=0;$m<5;$m++){
				$photosapi="https://maps.googleapis.com/maps/api/place/photo?maxwidth=750&photoreference=".$allphotos['result']['photos'][$m]['photo_reference']."&key=AIzaSyDgJbDwjCXxD4W4Xq9SZbDKlq-VB_ZqMPI";
				$fileContent_photos=file_get_contents($photosapi) or die("try again");			
				file_put_contents("pic".$m.".png", $fileContent_photos);								
			}
		}
		elseif (count($allphotos['result']['photos'])>0) {
			for($n=0;$n<count($allphotos['result']['photos']);$n++){
				$photosapi="https://maps.googleapis.com/maps/api/place/photo?maxwidth=750&photoreference=".$allphotos['result']['photos'][$n]['photo_reference']."&key=AIzaSyDgJbDwjCXxD4W4Xq9SZbDKlq-VB_ZqMPI";
				$fileContent_photos=file_get_contents($photosapi) or die("try again");				
				file_put_contents("pic".$n.".png", $fileContent_photos);				
			}
		}
		else{
			return;
		}
	}

?>



<html>
<head>
	<meta charset="UTF-8" http-equiv="Access-Control-Allow-Origin" content="*">
	<title>PHP for hw6</title>
	<style type="text/css">
		#form_border{
			margin-left: 25%;
			width: 700px;
			border-color: rgb(204,204,204);
			border-width: 4px;
			border-style: solid;
			background-color: rgb(246,246,246);
		}
		#map {
        height: 300px;
        width: 500px;
       }
	</style>	
</head>

<body onload="getip()">
	<fieldset id="form_border" > 
	<h1 align="center"><b><i>Travel and Entertainment Search</i></b></h1>
	<hr>
	<form  name="myform" method="POST" onsubmit="return false;" action="" >
	<div>
		<b>Keyword</b>
		<input type="text" name="keyword" id="keyword" value="" required>
	</div>	
	<p></p>
	<div>
		<b>Category</b>
		<select   name="category" id="category">
			<option value="default" >default</option>
			<option value="cafe" >cafe</option>
			<option value="bakery">bakery</option>
			<option value="restaurant">restaurant</option>
			<option value="beauty salon">beauty salon</option>
			<option value="casino">casino</option>
			<option value="movie theater">movie theater</option>
			<option value="lodging">lodging</option>
			<option value="airport">airport</option>
			<option value="train station">train station</option>
			<option value="subway station">subway station</option>
			<option value="bus station">bus station</option>
		</select>
	</div>	
	<p></p>
	<div>
		<div style="float: left">
		<b>Distance(miles)</b>
		<input type="text" name="distance" id="distance" value="" placeholder="10" >
		<b>from</b>	
	    </div>
		<div style="float: left">
		<input type="radio" name="fromchoice"  checked="checked" onclick="radio_disable()" value="Here">Here<br>
		<input type="radio" name="fromchoice"  onclick="radio_disable()" value="location">
		<input type="text" name="location" id="location" value="" placeholder="location" required disabled>		
		</div>		
	</div>

	<div style="clear:both; margin-left:70px" >
	<input type="submit" value="Search" id="submitbutton" name="submitbutton" onclick="return send();" disabled >	
	<button onclick="clearall()">Clear</button>
	</div>			
	</form>
	</fieldset>
	<p></p>
	<div id="resultstable"></div>
	<div>
	<div id="map"></div>

	</div>
	
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAzT2p0XaVZwyDQwnGpXRoBGgVKSTyRL3I">
    </script>

    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB1bpWcvgbqEDcin6LRVowLecvEJePie9k">
    </script>
	
</body>
	
	<script type="text/javascript">

		function getip(){
			xmlhttp1=new XMLHttpRequest();			
			xmlhttp1.open("POST","http://ip-api.com/json",false);
			xmlhttp1.send();	
			jsonobj=JSON.parse(xmlhttp1.responseText);
			userlat=jsonobj.lat;
			userlon=jsonobj.lon;
			
			document.getElementById("submitbutton").removeAttribute("disabled");
			return;
		}

		function showmap(t){
			placelat=t.title;
			placelng=t.id;
			mappositionid=t.innerHTML.replace("'","");
			//alert(placelat+" "+placelng+" "+mappositionid);
			
			initMap();
		}

		function initMap() {
        	var uluru = {lat: placelat*1, lng: placelng*1};
       		var map = new google.maps.Map(document.getElementById(mappositionid), {
          	zoom: 14,
          	center: uluru
      		});
        document.getElementById(mappositionid).style.width="400px";
        document.getElementById(mappositionid).style.height="350px";

        if(document.getElementById(mappositionid).style.display=="block"){
        	document.getElementById(mappositionid).style.display="none";
        }
        else{
        	document.getElementById(mappositionid).style.display="block";
        }
        if(document.getElementById("direction"+mappositionid).style.display=="block"){
        	document.getElementById("direction"+mappositionid).style.display="none";
        }
        else{
        	document.getElementById("direction"+mappositionid).style.display="block";
        }
        directionoptions="<div style='background-color:rgb(240,240,240)'><span onclick='walkdirection()'>Walk there</span><br>";
        directionoptions+="<span onclick='bikedirection()'>Bike there</span><br>";
        directionoptions+="<span onclick='drivedirection()'>Drive there</span></div>";
        document.getElementById("direction"+mappositionid).innerHTML=directionoptions;
        
        var marker = new google.maps.Marker({
          position: uluru,
          map: map
        });
     	}

     	function walkdirection(){
     		way="WALKING";
     		initMap1();
     	}

     	function bikedirection(){
     		way="BICYCLING";
     		initMap1();
     	}

     	function drivedirection(){
     		way="DRIVING";
     		initMap1();
     	}

     	function initMap1() { 	
        if(myform.fromchoice.value=="Here"){
        	startlat=userlat;
        	startlng=userlon;
        }
        else{
        	startlat=addresslat;
        	startlng=addresslng;
        }
        //console.log(startlat);
        var directionsDisplay = new google.maps.DirectionsRenderer;
        var directionsService = new google.maps.DirectionsService;
        var map1 = new google.maps.Map(document.getElementById(mappositionid), {
          zoom: 14,
          center: {lat: startlat*1, lng: startlng*1}
        }
        );
        directionsDisplay.setMap(map1);
        calculateAndDisplayRoute(directionsService, directionsDisplay);       
      }

     	function calculateAndDisplayRoute(directionsService, directionsDisplay) {       		
        directionsService.route({
          origin: {lat: startlat*1, lng: startlng*1},  
          destination: {lat: placelat*1, lng: placelng*1},          
          travelMode: way
        }, function(response, status) {
          if (status == 'OK') {
            directionsDisplay.setDirections(response);
          } else {
            window.alert('Directions request failed due to ' + status);
          }
        });
      }

		function send(){
			var keyword=document.getElementById("keyword").value;
			if(keyword==""){return; }
			var category=document.getElementById("category").value;
			var distance=document.getElementById("distance").value;
			var location=document.getElementById("location").value;
			var fromchoice=myform.fromchoice.value;
			if(myform.fromchoice.value!="Here"&&location==""){return;}

			var xmlhttp=new XMLHttpRequest();
			url="hw6final.php?keyword="+keyword+"&category="+category+"&distance="+distance+"&fromchoice="+fromchoice+"&location="+location+"&lat="+userlat+"&lng="+userlon;		
			xmlhttp.open('GET',url,true);
			xmlhttp.onreadystatechange=function(){
            	if(xmlhttp.readyState==4){
          		  if(xmlhttp.status==200){                                        		      
               		//console.log(xmlhttp.responseText);    
               		json_results=xmlhttp.responseText;     
					var table=JSON.parse(json_results);
					
					if(table.found=="no"){
						document.getElementById("resultstable").innerHTML="<div id='nothing' align='center' style='width: 800px;margin-left:22.5% ;border-color: rgb(204,204,204);background-color: rgb(246,246,246);border-style: solid;'>No Records has been found</div>";
						return;
					}

					addresslat=table.mylat;  //from location
               		addresslng=table.mylng;	   

					if(table.results.length>0){
					table_text="<table border='2' width=1300px align='center'><tbody><tr><th>Category</th><th>Name</th><th>Address</th></tr>";
					for(i=0;i<table.results.length;i++){
						table_text+="<tr><td><img src='"+table.results[i].icon+"''>"+"</td>"
						table_text+="<td><span onclick='showdetail(this)' name='"+table.results[i].name+"' id='"+table.results[i].place_id+"'>"+table.results[i].name+"</span></td>";
						table_text+="<td><span onclick='showmap(this)' title='"+table.results[i].geometry.location.lat+"' id='"+table.results[i].geometry.location.lng+"'>"+table.results[i].vicinity+"</span><br><div style='position:absolute;z-index=1' id='"+table.results[i].vicinity.replace("'","")+"'></div><div id='direction"+table.results[i].vicinity.replace("'","")+"' style='position:absolute; z-index=2'></div></td></tr>";
						//console.log(table.results[i].geometry.location.lat+" "+table.results[i].geometry.location.lng);
					}
					table_text+="</tbody></div>";
					document.getElementById("resultstable").innerHTML=table_text;
					}
					else{
					document.getElementById("resultstable").innerHTML="<div id='nothing' align='center' style='width: 800px;margin-left:22.5% ;border-color: rgb(204,204,204);background-color: rgb(246,246,246);border-style: solid;'>No Records has been found</div>";
					}        		               	                
          		  }
           		}
        	}        
       		xmlhttp.send();

      		return false;
		}

		function showreviews(){
			xml1=new XMLHttpRequest();
			xml1.open('GET',"hw6final.php?review=ok&start=start&placeid="+placeid,true);
			xml1.onreadystatechange=function(){
            if(xml1.readyState==4){
          	  if(xml1.status==200){
            json_reviews=xml1.responseText;
            //console.log(json_reviews);
			var allreviews=JSON.parse(json_reviews);
				
			rtable_text="<div align='center'>";
			rtable_text+="<p><b>"+placename+"</b></p><br>";
			rtable_text+="<p>click to hide reviews</p><img height='25px' onclick='showarrow()' src='http://cs-server.usc.edu:45678/hw/hw6/images/arrow_up.png'><br>";
			rtable_text+="<table border='2' style='border-collapse:collapse' width=800px align='center'><tbody>";			
			if (!allreviews.result.hasOwnProperty("reviews")) {
				rtable_text+="<th>No Reviews Found</th></tbody></table>";
			}
			else if(allreviews.result.reviews.length==0){
				rtable_text+="<th>No Reviews Found</th></tbody></table>";
			}
			else{
				
				for(j=0;j<allreviews.result.reviews.length&&j<5;j++){
					if(!allreviews.result.reviews[j].hasOwnProperty("profile_photo_url")){
						rtable_text+="<tr><td align='center'><b>"+allreviews.result.reviews[j].author_name+"</b></td></tr>";
					}
					else{
					rtable_text+="<tr><td align='center'><img width='50px' height='50px' src='"+allreviews.result.reviews[j].profile_photo_url+"'><b>"+allreviews.result.reviews[j].author_name+"</b></td></tr>";
					}
					rtable_text+="<tr><td>"+allreviews.result.reviews[j].text+"</td></tr>";
				}
				rtable_text+="</tbody></table>";
			}
			
			rtable_text+="<p>click to show photos</p><img height='25px' onclick='showphotos()' src='http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png'><br>";
			rtable_text+="</div>";
			document.getElementById("resultstable").innerHTML=rtable_text;
          		   }
          		}
          	}		
			xml1.send();			
		}

		function showphotos(){
			xml2=new XMLHttpRequest();
			xml2.open('GET',"hw6final.php?photo=ok&start=start&placeid="+placeid,true);
			xml2.onreadystatechange=function(){
            if(xml2.readyState==4){
          	  if(xml2.status==200){
          	json_photos=xml2.responseText;
			var allphotos=JSON.parse(json_photos);

			table_text="<div align='center'>";
			table_text+="<p><b>"+placename+"</b></p><br>";
			table_text+="<p>click to show reviews</p><img height='25px' onclick='showreviews()' src='http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png'><br>";
			table_text+="<p>click to hide photos</p><img height='25px' onclick='showarrow()' src='http://cs-server.usc.edu:45678/hw/hw6/images/arrow_up.png'><br>";
			table_text+="<table border='2' style='border-collapse:collapse' width=750px align='center'><tbody>";
			if(!allphotos.result.hasOwnProperty("photos")){
				table_text+="<th>No Photos Found</th></tbody</table>";
			}
			else if(allphotos.result.photos.length==0){
				table_text+="<th>No Photos Found</th></tbody</table>";
			}
			else{
				for(k=0;k<allphotos.result.photos.length&&k<5;k++){					
					link="pic"+k+".png?ver="+Math.random();
					// link="pic"+k+".png";					
					// link="pic"+k+".png"；
					table_text+="<tr><td style='padding:15px'><img style='width:750px' onclick='fullphoto(this)' src='"+link+"'></td></tr>";
				}
			}			
			table_text+="</tbody</table>";		
			table_text+="</div>";
			document.getElementById("resultstable").innerHTML=table_text;
          		 }
          	  }
          	}
			xml2.send();			
		}

		function fullphoto(t){
			window.open(t.src);
		}

		function showarrow(){
			table_text="<div align='center'>";
			table_text+="<p><b>"+placename+"</b></p><br>";
			table_text+="<p>click to show reviews</p><img height='25px' onclick='showreviews()' src='http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png'><br>";
			table_text+="<p>click to show photos</p><img height='25px' onclick='showphotos()' src='http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png'><br>";
			table_text+="</div>";
			document.getElementById("resultstable").innerHTML=table_text;
		}

		function showdetail(the){
			placeid=the.id;
			placename=the.innerHTML;

			table_text="<div align='center'>";
			table_text+="<p><b>"+placename+"</b></p><br>";
			table_text+="<p>click to show reviews</p><img height='25px' onclick='showreviews()' src='http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png'><br>";
			table_text+="<p>click to show photos</p><img height='25px' onclick='showphotos()' src='http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png'><br>";
			table_text+="</div>";
			document.getElementById("resultstable").innerHTML=table_text;

			url="hw6final.php?placeid="+placeid+"&start=start";		
			xmlhttp2=new XMLHttpRequest();
			xmlhttp2.open('GET',url,false);	    
       		xmlhttp2.send();   		
		}
		
		function clearall(){	
			document.getElementById("keyword").value="";
			document.getElementById("distance").value="";
			document.getElementById("location").value="";
			var radios=document.getElementsByName("fromchoice");
			radios[0].checked="checked";		
			document.getElementById("location").setAttribute("disabled","disabled");
			
			myform.category.value="default";
			document.getElementById("resultstable").innerHTML="";
		}

		function radio_disable(){
			var radios=document.getElementsByName("fromchoice");
			if(radios[0].checked==true){
				document.getElementById("location").setAttribute("disabled","disabled");
			}
			else{
				document.getElementById("location").removeAttribute("disabled");
			}	
		}
	</script>

	

</html>