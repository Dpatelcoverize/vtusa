<?php



?>

<html>
	<head>
		<title>signature tests</title>
		<script src="./js/jSignature/jSignature.min.js"></script>
		<script src="./js/jSignature/jSignInit.js"></script>
		<script src="./js/custom-validation.js"></script>
		<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
		<script src="https://cdn.rawgit.com/brinley/jSignature/master/libs/jSignature.min.js"></script>

		<script>
			$(document).ready(function() {
			var dataString;
			  $("#SignatureController").jSignature({
				'decor-color': 'transparent',
				'lineWidth': 1,
			  });
				$('#Getsign').click(function () {
							dataString = $("#SignatureController").jSignature("getData","base30");
							alert(dataString);
						});
						$('#Printsign').click(function () {
							var dataString = $("#SignatureController").jSignature("getData","base30");
							alert(dataString);
							$('#PrintSignatureController').append("<img class='imported' src='" + dataString + "'></img>");
						});
			});
		</script>

		<style>
			#SignatureController
			{
				width: 500px;
			  height: 150px;
			  border: 1px solid black;
			}
		</style>
	</head>
	<body>

		<h2>Signature Test</h2>
		<div id="SignatureController">

		</div>

		<br>
		<h2>Print Test</h2>
		<div id="PrintSignatureController">

		</div>
		<button id="Getsign">Get Singture </button>
		<button id="Printsign">Print Singture </button>


<?php if(false){ ?>
	   <div id="displaySignature">
	   </div>
	   <script>
			 $(document).ready(function(data){
			   var i = new Image()
			   var signature = signatureDataFromDataBase;
		//Here signatureDataFromDataBase is the string that you saved earlier
				i.src = 'data:' + signature;
				$(i).appendTo('#displaySignature')
			   })
	   </script>
<?php } ?>
	</body>
</html>