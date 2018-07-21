var oBoard=document.getElementsByClassName('board')[0];
var oGameBtn=document.getElementsByClassName('gameBtn')[0];
var oAlertBoard=document.getElementsByClassName('alertBoard')[0]
var oDelete=document.getElementsByClassName('deleteBtn')[0]
var aSpan=document.getElementsByTagName('span')
var oFlags=document.getElementsByClassName('flags')[0]
var aGird=null;
var gameTime=0;
var startGame=true;
var grand={
	rows:10,
	cols:10,
	grandWidth:parseInt( window.getComputedStyle(oBoard, false)['width']),
	grandHeight:parseInt( window.getComputedStyle(oBoard, false)['height']),
	arr:[]
}
var thunder={
	num:10,
	flags:10,
	k:0,
	arr:[]
}

// 点击开始游戏按钮
	oGameBtn.onclick=function(){
		if(startGame){
			oFlags.style.display='block';
			aSpan[1].innerHTML=gameTime
			aSpan[0].innerHTML=thunder.flags;
			oBoard.style.borderLeft = '1px solid orange';
			oBoard.style.borderTop=	'1px solid orange';
			createGrand();
			pushThunder();
			aroundThunder();
			timer=setInterval(function(){
				gameTime++
				aSpan[1].innerHTML=gameTime
			}, 1000)
			startGame=false;

		}
	
}


// 禁用右键点击事件
	oAlertBoard.oncontextmenu=oBoard.oncontextmenu=function(){
	return false
}
	
	oDelete.onclick=function(){
		deleteBoard()
		return false;
	}

	
	


// 创建网格
function createGrand(){
	grand.arr=[]
	for(var i=0;i<grand.rows;i++){
		for(var j=0;j<grand.cols;j++){
			oGrid=document.createElement('div');
			oGrid.style.width=grand.grandWidth/grand.cols-1+'px';
			oGrid.style.height=grand.grandHeight/grand.rows-1+'px';
			oGrid.classList.add('gridBorder');
			oGrid.id=i+'-'+j
			 grand.arr.push({mine:0})
			oBoard.appendChild(oGrid)
			
		}
	}
	aGird=document.getElementsByClassName('gridBorder')


}

// 随机设置地雷
function pushThunder(){
	var n=0;
	 thunder.k=thunder.num
	while(thunder.num){
		var mineIndex=Math.floor(Math.random()*grand.rows*grand.cols);
		if(grand.arr[mineIndex].mine===0){
		aGird[mineIndex].classList.add('isThunder');
		grand.arr[mineIndex].mine=1;
		thunder.arr.push(mineIndex);
		thunder.num--
		}
	}

}


function aroundThunder(){
	// oBoard.onmousedown=function(e){
	// 	var e=e || window.event;
	// 	if(e.button==0){	
	// 		// oBoard.addEventListener('click', function(){
	// 		// 	leftClick(e.target)
	// 		// },true)
	// 		for(var i=0;i<aGird.length;i++){
	// 			aGird[i].onclick=function(){
	// 				if(this.classList.contains('isThunder')==true){
	// 					gameover()
	// 					return false
	// 						}
	// 				else{
	// 					leftClick(this)
	// 				}
					
	// 			}
				
	// 		}
	// 	}else if(e.button==2){
	// 		rightClick()
	// 	}
		
	// }
	for(var i=0;i<aGird.length;i++){
		aGird[i].onmousedown=function(){
			var e=e || event;
			if(e.button==0){
				if(this.classList.contains('isThunder')==true && this.readyRightClick!=true){
						gameover()
						return false
							}
					else{
						if(this.readyRightClick!=true){
							leftClick(this)
						}
						
					}
			}else if(e.button==2){
				if(this.readyLeftClick!=true){
					rightClick(this)
				}
			}
			
	}
		}
	
}
// 点击左键触发事件
function leftClick(elm){
	elm.readyLeftClick=true;
	if(elm.classList.contains('isThunder')==true){
		return
	}
	var n=0;
	var oId=elm.id;
	idArr=oId.split('-');
	var posX=+idArr[0];
	var posY=+idArr[1];
			for(var i=posX-1;i<=posX+1;i++){
			for(var j=posY-1;j<=posY+1;j++){
			var aroundThunders=document.getElementById(i+'-'+j)
			 // console.log(aroundThunders)
			if(aroundThunders && aroundThunders.classList.contains('isThunder')){
				n++
				
			}
			
		}
		
	}

	
 elm.classList.add('num');
 elm.style.height =elm.style.lineHeight= grand.grandHeight/grand.rows-1+'px';
 elm && (elm.classList.add('nba-'+n));
 elm.innerHTML=n;
	if(n==0){
		for(var i=posX-1;i<=posX+1;i++){
			for(var j=posY-1;j<=posY+1;j++){
				var nearGrid=document.getElementById(i+'-'+j)
				if(nearGrid && nearGrid.length!=0){
					if(!nearGrid.classList.contains('check')){
						nearGrid.classList.add('check');
						leftClick(nearGrid)

					}
				}
			}
		}
	}
	
}

// 点击右键触发事件
function rightClick(elm){
	elm.readyRightClick=true
	var e=e || window.event;
	if(e.target.classList.contains('isThunder')){
		if(e.target.classList.contains('flag')){
			thunder.flags++
		}else{
			thunder.flags--	
		}
	aSpan[0].innerHTML=thunder.flags
		e.target.classList.toggle('flag')

		if(thunder.flags==0){
			winGame()
		}
	}else{
		gameover()
	}
}
function gameover(){
	setTimeout(function(){
		clearInterval(timer)
		oAlertBoard.style.display = 'block';
		oAlertBoard.style.background = 'url("images/gameover2.jpg") ';
		oAlertBoard.style.backgroundSize = '100% 100%';
		var len=thunder.arr.length
		for(var i=0;i<len;i++){
		aGird[thunder.arr[i]].classList.add('thunderBoom');

	}
}, 300)
	
}

function winGame(){
	setTimeout(function(){
		clearInterval(timer)
		oAlertBoard.style.display = 'block';
		oAlertBoard.style.backgroundImage = 'url("images/success2.jpg")';
		oAlertBoard.style.backgroundSize = '100% 100%';
	},300)
	
}
// 点击删除按钮
function deleteBoard(){
gameTime=0
oFlags.style.display = 'none'
startGame=true;
thunder.arr=[];
thunder.num=thunder.k
thunder.flags=thunder.k
oAlertBoard.style.display='none';
oBoard.style.border = 'none';
oBoard.innerHTML=''
}