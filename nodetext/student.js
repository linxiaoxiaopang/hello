var fs = require('fs')
var dePath = './db.json'


/**
 * 获取学生列表
 * @param  {function} 回调函数
 */
// exports.find=function(comeback){
// 	fs.readFile(dePath,'utf8', function(err, data){
// 		if(err){
// 			return comeback(err)
// 		}
// 		var resultData=JSON.parse(data).student
// 		comeback(null,resultData)
// 	})
// }
/**
 * 读取文件数据
 * @param  {Function} callback 回调函数
 */
function crud_readFile(callback){
	fs.readFile(dePath, function(err, data){
		if (err){
			return callback(err)
		}
		callback(null, JSON.parse(data).student)
	})
}
/**
 * 数据写入文件
 * @param  {Object} obj 所需写入的数据
 * @return {Functionn} callback 回调函数
 */
function crud_writeFile(obj, callback){
	fs.writeFile(dePath, obj, function(err) {
    if (err) {
        return callback(err)
    }
	})
	callback(null)
}


exports.find=function(comeback){
	crud_readFile(function(err, data){
		if (err){
			return comeback(err)
		}
		comeback(null, data)

	})
}

/**
 * 添加保存学生
 * @param {object} student 学生对象
 * @param {function} cameback 回到函数
 */

// exports.save=function(addInfo,cameback){
// 	fs.readFile(dePath,'utf8', function(err, data){
// 		if(err){
// 			return comeback(err)
// 		}
// 		var resultData=JSON.parse(data).student
// 		addInfo.id=parseInt(resultData[resultData.length-1].id) + 1
// 		resultData.push(addInfo)
// 		var resultJson={
// 			student : resultData
// 		}
// 		resultJson =JSON.stringify(resultJson)
// 		fs.writeFile(dePath, resultJson, function(err){
// 			if(err){
// 				return comeback(err)
// 			}
// 		})
// 		cameback(null,resultData)
// 	})
// }

exports.save=function(addInfo, cameback){
	crud_readFile(function(err, data){
		if (err){
			return callback(err)
		}
		addInfo.id=parseInt(data[data.length-1].id) + 1
		data.push(addInfo)
		resultJson=JSON.stringify({
			student : data
		})
		
		crud_writeFile(resultJson, function(err){
			if(err){
				return cameback(err)
			}
			cameback()
		})
	})
}

/**
 * 根据id回去学生信息对象
 * @param  {Number} id  学生id
 * @param  {[function]} cameback 回调函数
 */
exports.findById=function(id,cameback){
	fs.readFile(dePath,'utf8', function(err, data){
		if(err){
			return comeback(err)
		}
		var resultData=JSON.parse(data).student
		var reditInfo=resultData.find(function(item){
			return item.id === parseInt(id)
		})

		cameback(null,reditInfo)
	})
}

/**
 * @param  {Object} obj 学生对象 
 * @param  {Function} cameback 回调函数
 */
exports.reditById=function(obj,cameback){
	fs.readFile(dePath,'utf8', function(err, data){
		if(err){
			return cameback(err)
		}

		var resultData=JSON.parse(data).student
		var index=resultData.findIndex(function(item){
			return item.id === parseInt(obj.id)
		})
		for(var key in obj){
			resultData[index][key]=obj[key]
		}
		// console.log(resultData[index])
		var dataJson={
			student: resultData
		}
		
		dataJson = JSON.stringify(dataJson)
		fs.writeFile(dePath, dataJson, function(err){
			if(err){
				return cameback(err)
			}
			console.log(dataJson)
			cameback(null, dataJson)
		})
		
	})
}

/**
 * @param  {Number} id 学生ID
 * @param  {Function} Function 回调函数
 */
exports.deleteById=function(id,cameback){
	fs.readFile(dePath,'utf8', function(err, data){
		if(err){
			return comeback(err)
		}
		var resultData=JSON.parse(data).student
		var index = resultData.findIndex(function(item){
			return item.id === parseInt(id)
		})
		resultData.splice(index,1)
		dataJson={
			student: resultData
		}
		var dataJsonStr = JSON.stringify(dataJson)
		fs.writeFile(dePath, dataJsonStr, function(err){
			if(err){
				return cameback(err)
			}
			console.log(dataJson)
			cameback()
		})
		
	})
}


