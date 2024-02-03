//control google piechart
function chartFunction() {
    if(window.location.href.indexOf('admindashboard') !== -1){
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);
    } 
}

function drawChart() {
    var chartData= document.getElementById('categ-data');
    var chartData2= document.getElementById('cont-data')
        var categories = parseInt(chartData.dataset.numCategories);
        var contents = parseInt(chartData2.dataset.numContents);
        var data = google.visualization.arrayToDataTable([
            ['data', 'value'],
            ['categories', categories],
            ['contents', contents]
        ]);
    
        var options = {
            colors:['green', 'blue'],
            height: 500,
            width: 900

        };
    
        var chart = new google.visualization.PieChart(document.getElementById('draw-chart'));
        chart.draw(data, options);
}
chartFunction()

//control admin View url
const dashboardClick = document.getElementById('dashboard-panel');
const addCategoryClick = document.getElementById('add-category-panel');
const viewCategoryClick = document.getElementById('view-category-panel');
const addContentClick = document.getElementById('add-content-panel');
const viewContentClick = document.getElementById('view-content-panel');
//dashbord chart view
dashboardClick.addEventListener('click', function (){
    window.location.href = 'http://localhost/VasHouseAssessment/public/index.php?url=admindashboard';
})
// add category view
addCategoryClick.addEventListener('click', function(){
    window.location.href = 'http://localhost/VasHouseAssessment/public/index.php?url=addcategories';
})
// categories table view
viewCategoryClick.addEventListener('click', function() {
    window.location.href = 'http://localhost/VasHouseAssessment/public/index.php?url=viewcategories';
})
//add content view
addContentClick.addEventListener('click', function () {
    window.location.href = 'http://localhost/VasHouseAssessment/public/index.php?url=addcontents';
})
viewContentClick.addEventListener('click' , function(){
    window.location.href = 'http://localhost/VasHouseAssessment/public/index.php?url=viewcontents';
})
