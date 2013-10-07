var smiles = 
[
{ smile : ':)'       , image : '1.gif'},
{ smile : ':('       , image : '2.gif'},  
{ smile : ';)'       , image : '3.gif'},
{ smile : ':beer:'   , image : '4.gif'},
{ smile : ':like:'   , image : '5.gif'},
{ smile : ':wall:'   , image : '6.gif'},
{ smile : ':D'       , image : '7.gif'},
{ smile : ':shy:'    , image : '8.gif'},
{ smile : ':secret:' , image : '9.gif'},
{ smile : ':party:'  , image : '10.gif'}		  
]

function StringWithSmiles(text) {

	for (var i=0; i<=smiles.length-1; ++i) 
		 text = text.replaceAll(smiles[i].smile,'<img src="' + base_url + way_style + 'img/smiles/' + smiles[i].image + '" alt="Смайлик" />')
		 
  return text	 
}