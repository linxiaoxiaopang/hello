$(function(){
	var vegStr = location.href.split('?')[1].split('&')
	// console.log(vegStr)
	var houseId = vegStr[0].split( '=' )[1]
	var vegId = vegStr[1].split( '=' )[1]
	var vegIndex = vegStr[2].split( '=' )[1]

	/**
	*初始化野狗数据库节点
	**/
	var config = {
		  syncURL: "https://wd0963251028wmtevt.wilddogio.com/vegData" //输入节点 URL
		}
		wilddog.initializeApp(config)
		var ref = wilddog.sync().ref()
	/* 
	*snapshot 里面的数据会一直和云端保持同步
	*/
	ref.on("value", function(snapshot) {
	    // console.log(snapshot.val())
	    // vegsData = snapshot.val()[houseId] //获取野狗数据库所选大棚的数据
	   var vegData = null
	   var  detailData =null
	   if (vegId.indexOf('F')){
	   	 vegData = snapshot.val()[houseId]['firstLine'][vegId].vegs[vegIndex]
	   }else{
	     vegData = snapshot.val()[houseId]['secondLine'][vegId].vegs[vegIndex]
	   }
	   var timeStr = 1000*60*60*24
	   var dateDis = new Date() - new Date(vegData.sowDate)
	   vegData.passDay = parseInt(dateDis/timeStr)
	   vegData.upComingDay = vegData.estimate - vegData.passDay
	   vegData.metureDate = getMatureDate(vegData.sowDate, vegData.estimate )
	 	
	   $('.pro_title').html(vegData.name + '详情表')
	    console.log(vegData)
	    var detailHtmlStr = template('vegDetailTpl', {
	    	data : vegData
	    })

	    $('#vegDetail').html(detailHtmlStr)

	   // console.log(detailHtmlStr)
	})

	$('#vegDetail').on('click', '#save',  function(){
		history.back()
	})
})

function getMatureDate(originDate, addDays){
   var timeStr = 1000*60*60*24
   var matureDateStr = new Date(originDate).getTime() + addDays * timeStr 
   var originDate = new Date(matureDateStr)
   var matureDate = originDate.getFullYear() + '-' + parseInt(originDate.getMonth()+ 1) + '-' + originDate.getDate() 
	   return matureDate
}
