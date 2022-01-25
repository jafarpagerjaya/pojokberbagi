var data = {
    datasets: [{
        data: [10, 20, 30],
        backgroundColor: [
            '#2dce89',
            '#fb6340',
            '#f4f5f7'
        ]
    }],
    labels: [
        'Donasi',
        'Lihat',
        'Batal'
    ]
};

let ctx = document.getElementById("myChart");

let myPieChart = new Chart(ctx, {
    type: 'pie',
    data: data
});