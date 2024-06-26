const { format, parseISO } = require('date-fns');
const { pt } = require('date-fns/locale')

$(() => {
   window.location.pathname == "/home" && initChart();
});

const randomColors = () => {
    const lettersHexaDecimal = "0123456789ABCDEF";
    let color = '#';

    for(let i = 0; i < 6; i++){
        color += lettersHexaDecimal[Math.floor(Math.random() * 16)]
    }

    return color;
}

const initChart = () => {
    $.get("/lancamentos/getGastosGraphs",
        function (response, textStatus, jqXHR) {
            renderGraph(response);
        },
        "JSON"
    );
}

const renderGraph = (data) => {
    const arrayDates = Object.keys(data)

    if(arrayDates.length === 0){
        $("#emptyRegisters").show();
        $("#graphLancamenots").hide();

        return;
    }

    const months = arrayDates.map(value => (
        format(parseISO(value), 'MMMM', {
            locale: pt
        })
    ));

    const gastos = arrayDates.map((month, index) => {
        const array = data[month].map(gastos => (parseFloat(gastos.valor)))
        return array.reduce((total, next) => (total + next))
    });
    const colors = arrayDates.map(() => randomColors());

    new Chart($("#myChart"),{
        type: 'bar',
        data: {
            labels: months,
            datasets:[
                {
                    label: '',
                    data: gastos.map(gasto => Number((gasto.toFixed(1)))),
                    backgroundColor:colors,
                },
            ]
        },
        options:{
            scales:{
                yAxes:[
                    {
                        ticks:{
                            beginAtZero: true,
                        }
                    },
                ],
            },
        }
    });
}
