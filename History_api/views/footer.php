<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script>
$(document).ready(function(){
      var nav,content,fetchAndInsert;
     
      nav=$('#main');
      content=$('#content');
      fetchAndInsert=function(href){
           $.ajax({
                 url:'http://localhost/mk/History_api/content/'+href.split('/').pop(),
                 method:'GET',
                 cache:false,
                 success:function(data){
                       content.html(data);
                 },
           });
      };
      $(window).on('popstate',function(){
             fetchAndInsert(location.pathname);
      });
      nav.find('a').on('click',function(e){
                       var href=$(this).attr('href');
                      // manipualte history
                      console.log(href);
                      history.pushState(null,null,href);
                      //fetch and insert
                      fetchAndInsert(href);
                      e.preventDefault();
      });
});



</script>