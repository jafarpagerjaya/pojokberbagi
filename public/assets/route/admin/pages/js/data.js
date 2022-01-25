var ctx = document.getElementById("myChart");

var data = {
    labels: ["Agus","Sept","Octo"],
    datasets: [
        {
            label: "Donasi ",
            fill: false,
            lineTension: 0.1,
            backgroundColor: "rgba(75,192,192,0.4)",
            borderColor: "rgba(75,192,192,1)",
            borderCapStyle: 'butt',
            borderJoinStyle: 'miter',
            borderWidth: 1,
            pointBorderColor: "rgba(75,192,192,1)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 5,
            pointHoverRadius: 10,
            pointHoverBackgroundColor: "rgba(75,192,192,1)",
            pointHoverBorderColor: "rgba(220,220,220,1)",
            pointHoverBorderWidth: 5,
            pointRadius: 5,
            pointHitRadius: 10,
            data: [1, 3, 2],
        }
    ]
};

var myLineChart = new Chart(ctx, {
    type: 'line',
    data: data,
});