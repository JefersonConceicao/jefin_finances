$(() => {
    initChart();
});

const initChart = () => {
   const myGraph = $("#myChart");
   const chart = new Chart(myGraph, {
        type: 'bar',
        data: {
            labels: ['Janeiro', 'Fevereiro', 'Março'],
            datasets:[{
                label: 'Comparativo de gastos',
                data: [20,20,20],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.4)',
                    'rgba(255, 206, 86, 0.3)',
                ]
            }]
        },
   });
}