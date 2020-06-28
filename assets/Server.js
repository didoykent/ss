var app = require('express');

var server = require('http').Server(app);

var io = require('socket.io')(server);

var redis = require('redis');

var moment = require('moment');

var FormData = require('form-data');
const axios = require('axios');



setInterval(() => {
migrate_tutor_data()

},100000)

setInterval(() => {
migrate_student_data()

},150000)

setInterval(() => {
migrate_student_classes()

},240000)

setInterval(() => {
get_and_update_cancelled_class()

},480000)

setInterval(() => {
get_tutor_classes()

},300000)

setInterval(() => {

update_tutor_classes()
},360000)


function migrate_tutor_data(){

  axios.get('http://localhost/lms/Uedu/migrate_tutor_data').then (res => {

    if(res.data){
      console.log(res.data)
    }
}).catch(err => {

  console.log(err)
})

}
function migrate_student_data(){

  axios.get('http://localhost/lms/Uedu/migrate_student_data').then (res => {

  if(res.data){
    console.log(res.data)
  }
}).catch(err => {

console.log(err)
})
}
function migrate_student_classes(){
  axios.get('http://localhost/lms/Uedu/migrate_student_classes').then (res => {

  if(res.data){
    console.log(res.data)
  }
}).catch(err => {

console.log(err)
})
}
function get_and_update_cancelled_class(){
  axios.get('http://localhost/lms/Uedu/get_and_update_cancelled_class').then (res => {

    if(res.data){
      console.log(res.data)
    }
}).catch(err => {

  console.log(err)
})

}
function get_tutor_classes(){




    axios.get('http://localhost/lms/Uedu/student_add_class').then (res => {

      if(res.data){

        let Classes = JSON.parse(res.data.substring(2))

if(Classes){
        let length = Classes.length / 10

            let test = Classes.length
            let data = 0








        for(var i=0; i<10; i++){

          let  newTimeout = 5000 * i *5

          setTimeout(() =>{





          test-= length
          data+= length

      let   newArray = Classes.slice(0, test)
    newArray.splice(0, newArray.length - length )





  console.log(test,'test')
  console.log(data, 'data')













      newArray.forEach((item, i) => {
          console.log(item.applicable_days,'nw')

          if(!IsJsonString(newArray[i]['applicable_days'])){

            newArray[i]['applicable_days'] = JSON.stringify(newArray[i]['applicable_days'].split(','))
          }

      newArray[i]['applicable_days'] = newArray[i]['applicable_days'] ?  JSON.parse(item.applicable_days).filter( obj => obj) : ''

      let start_class = moment.unix(newArray[i]['start_class']).format('YYYY-MM-DD')
      newArray[i]['date'] = start_class
      let end_class = moment.unix(newArray[i]['end_class']).format('YYYY-MM-DD')

      var mom = moment({hour: newArray[i]['hour'], minute:newArray[i]['minute']}).format('HH:mm:ss')
     let date  = start_class + ' '
      let add = moment(mom, 'HH:mm:ss').add(parseInt( newArray[i]['duration'].replace('minutes', '')), 'minutes').format('HH:mm:ss')
     let start_date =  date + mom

     let end_date = date + add

    let test = ''



      let weekdays = []

      for(var c =0; c<newArray[i]['applicable_days'].length; c++){

        if( newArray[i]['applicable_days'][c] === 'M'){

          weekdays.push(1)
        }

        if( newArray[i]['applicable_days'][c] === 'T'){

         weekdays.push(2)
       }

       if( newArray[i]['applicable_days'][c] === 'W'){

        weekdays.push(3)
      }

      if( newArray[i]['applicable_days'][c] === 'TH'){

       weekdays.push(4)
      }

     if( newArray[i]['applicable_days'][c] === 'F'){

      weekdays.push(5)
     }

      }

      let endPeriod = moment(date).add(newArray[i]['period']  , 'month').format('YYYY-MM-DD')

    let num = 1;

    let class_start = []
    let class_end = []
      if(weekdays.indexOf(moment(date).weekday()) != -1){

        test = true
        class_start.push(start_date)
         class_end.push(end_date)
      }

        let startPeriod = moment(date).format('YYYY-MM-DD')
    while(startPeriod <= endPeriod){



    startPeriod = moment(date).add(num++, 'd').format('YYYY-MM-DD')

      if(weekdays.indexOf(moment(startPeriod).weekday()) != -1){

      class_start.push(	startPeriod + ' ' + mom)
      class_end.push(startPeriod + ' ' + add)
    }
    }



    newArray[i]['applicable_days'] = JSON.stringify(newArray[i]['applicable_days']).replace(/"/g, '').replace('[', "").replace(']', "")
    newArray[i]['start_schedule'] = JSON.stringify(class_start).replace(/"/g, '').replace('[', "").replace(']', "")
    newArray[i]['end_schedule'] = JSON.stringify(class_end).replace(/"/g, '').replace('[', "").replace(']', "")

      });


      if(Classes){

        const form = new FormData


              console.log(newArray, 'c4')
      form.append('Classes', 'test')
      form.append('what', 'what')

      axios.post('http://localhost/lms/Uedu/create_tutor_class', {Classes: JSON.stringify(newArray)}).then(res => {



        console.log(res.data, 'tutorc')
      }).catch(err => {

        console.log(err, 'err')
      })





      }


}, newTimeout)

  }


      }
}

    }).catch(err => {

      console.log(err)
    })
}
function update_tutor_classes(){


  axios.get('http://localhost/lms/Uedu/student_add_class').then (res => {

    if(res.data){

      let Classes = JSON.parse(res.data.substring(2))

        if(Classes){
      let length = Classes.length / 10

          let test = Classes.length
          let data = 0








      for(var i=0; i<10; i++){

        let  newTimeout = 5000 * i *5

        setTimeout(() =>{





        test-= length
        data+= length

    let   newArray = Classes.slice(0, test)
  newArray.splice(0, newArray.length - length )





console.log(test,'test')
console.log(data, 'data')
  console.log(newArray,'nw')














    newArray.forEach((item, i) => {

    console.log(item.applicable_days,'nw')

      if(!IsJsonString(newArray[i]['applicable_days'])){

        newArray[i]['applicable_days'] = JSON.stringify(newArray[i]['applicable_days'].split(','))
      }

    newArray[i]['applicable_days'] = newArray[i]['applicable_days'] ?  JSON.parse(item.applicable_days).filter( obj => obj) : ''

    let start_class = moment.unix(newArray[i]['start_class']).format('YYYY-MM-DD')
    newArray[i]['date'] = start_class
    let end_class = moment.unix(newArray[i]['end_class']).format('YYYY-MM-DD')

    var mom = moment({hour: newArray[i]['hour'], minute:newArray[i]['minute']}).format('HH:mm:ss')
   let date  = start_class + ' '
    let add = moment(mom, 'HH:mm:ss').add(parseInt( newArray[i]['duration'].replace('minutes', '')), 'minutes').format('HH:mm:ss')
   let start_date =  date + mom

   let end_date = date + add

  let test = ''



    let weekdays = []

    for(var c =0; c<newArray[i]['applicable_days'].length; c++){

      if( newArray[i]['applicable_days'][c] === 'M'){

        weekdays.push(1)
      }

      if( newArray[i]['applicable_days'][c] === 'T'){

       weekdays.push(2)
     }

     if( newArray[i]['applicable_days'][c] === 'W'){

      weekdays.push(3)
    }

    if( newArray[i]['applicable_days'][c] === 'TH'){

     weekdays.push(4)
    }

   if( newArray[i]['applicable_days'][c] === 'F'){

    weekdays.push(5)
   }

    }

    let endPeriod = moment(date).add(newArray[i]['period']  , 'month').format('YYYY-MM-DD')

  let num = 1;

  let class_start = []
  let class_end = []
    if(weekdays.indexOf(moment(date).weekday()) != -1){

      test = true
      class_start.push(start_date)
       class_end.push(end_date)
    }

      let startPeriod = moment(date).format('YYYY-MM-DD')
  while(startPeriod <= endPeriod){



  startPeriod = moment(date).add(num++, 'd').format('YYYY-MM-DD')

    if(weekdays.indexOf(moment(startPeriod).weekday()) != -1){

    class_start.push(	startPeriod + ' ' + mom)
    class_end.push(startPeriod + ' ' + add)
  }
  }



  newArray[i]['applicable_days'] = JSON.stringify(newArray[i]['applicable_days']).replace(/"/g, '').replace('[', "").replace(']', "")
  newArray[i]['start_schedule'] = JSON.stringify(class_start).replace(/"/g, '').replace('[', "").replace(']', "")
  newArray[i]['end_schedule'] = JSON.stringify(class_end).replace(/"/g, '').replace('[', "").replace(']', "")

    });


    if(Classes){

      const form = new FormData


            console.log(newArray, 'c4')
    form.append('Classes', JSON.stringify(newArray))
    form.append('what', 'what')

    axios.post('http://localhost/lms/Uedu/update_tutor_class', {Classes: JSON.stringify(newArray)}).then(res => {



      console.log(res.data, 'tutorc')
    }).catch(err => {

      console.log(err, 'err')
    })





    }


}, newTimeout)

}
}

    }


  }).catch(err => {

    console.log(err, 'err')
  })
}

function IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

let Users = [];


server.listen(7740);

var redisClient = redis.createClient();

redisClient.subscribe('notif');
redisClient.subscribe('chat');

redisClient.on('message', function(channel, message){



if(message.trim() && channel === 'notif'){

const user = JSON.parse(message)

if(user){

  Users.push(user.conn_id)
}
console.log(Users, 'users')

}

if(message && channel === 'chat'){


   io.sockets.emit('chat_message', message)

  console.log(message,'message')


}

})
 io.on('connection', function(socket){

   console.log('Client connected')




   socket.on('student_writing_correction', function(message){

     if(message){

       io.sockets.emit('tutor_writing_correction', message)
     }

     console.log(message, 'correction')
   })


   socket.on('set_content_correction', function(message) {

     if(message){

       io.sockets.emit('tutor_corrected_content', message)

       console.log(message)
     }
   })

   socket.on('qa', function(message) {


     if(message){

       io.sockets.emit('student_qa', message)

       console.log(message)
     }
   })

   socket.on('qa_reply', function(message){

     if(message){

       io.sockets.emit('qa_answer', message)
       console.log(message)
     }
   })



   socket.on('disconnect', function(){


   })
 });
