<template>
  <h2>test</h2>
</template>

<script>
import axios from 'axios'
import moment from 'moment'
export default {

  data(){

  return {


  }
},

mounted(){



this.setTime()

},

methods: {

  setTime(){

    axios.get('Uedu/student_add_class').then (res => {

      if(res.data){

        let Classes = JSON.parse(res.data.substring(2))

                console.log(Classes ,'th')
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
  console.log(newArray)












      newArray.forEach((item, i) => {

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

      axios.post('http://localhost/lms/Uedu/create_tutor_class', form).then(res => {



        console.log(res.data, 'tutorc')
      }).catch(err => {

        console.log(err, 'err')
      })





      }


}, newTimeout)

  }


      }


    })
  },


  update_tutor_classes(){


    axios.get('Uedu/student_add_class').then (res => {

      if(res.data){

        let Classes = JSON.parse(res.data.substring(2))

                console.log(length ,'th')
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
  console.log(newArray)












      newArray.forEach((item, i) => {

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

      axios.post('http://localhost/lms/Uedu/update_tutor_class', form).then(res => {



        console.log(res.data, 'tutorc')
      }).catch(err => {

        console.log(err, 'err')
      })





      }


}, newTimeout)

  }


      }


    })


  }


}
}
</script>

<style lang="css" scoped>
</style>
