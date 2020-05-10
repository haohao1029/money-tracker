$(function () {
    $('.btn-eff').on('click', function () {
        if ($(this).attr('data-effect') == 'click-l') {
            clickEffect('#' + $(this).attr('id'), 50);
        } else {
            clickEffect('#' + $(this).attr('id'));
        }
    });

    $('.prev-month, .next-month, #current-month').on('click', function () {
        const value = $(this).val();
        const dateShowing = $('.showing-month').attr('data-value');
        $.ajax({
            type: "POST",
            url: "ajax/family-summary-change-year.php",
            data: {
                'action_val': value,
                'date_showing': dateShowing
            },
            success: function (response) {
                response = JSON.parse(response);
                const date = response['date'];
                const unix = response['unixdate'];
                const pieIncome = response['pieIncome'];
                const pieIncomeCategory = response['pieIncomeCategory'];
                const pieExpense = response['pieExpense'];
                const pieExpenseCategory = response['pieExpenseCategory'];
                const incomeBar = response['incomeBar'];
                const expenseBar = response['expenseBar'];

                $('.showing-month').attr('data-value', unix).html(date);

                if (checkMonthYearMatch(unix)) {
                    $('.next-month').prop('disabled', true);
                } else {
                    $('.next-month').prop('disabled', false);
                }

                //Bar
                $(".new").show();
                $(".old").hide();
                //Bar
                $("#bar").remove();
                if (window.newBars != undefined)
                    window.newBars.destroy();
                if (pieExpense == '' && pieIncome == '') {
                    $("#newBar").hide();
                    $("#error4").show();
                } else {
                    $("#newBar").show();
                    $("#error4").hide();
                    let arrBar = [];
                    arrBar.push(incomeBar)
                    arrBar.push(expenseBar)
                    let barColors = [];
                    let hoverBarColors = [];
                    for (i = 0; i < 2; i++) {
                        barColors.push(getColor());
                    }
                    for (i = 0; i < 2; i++) {
                        hoverBarColors.push(getColor());
                    }
                    window.newBars = new Chart(newBar, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: arrBar,
                                backgroundColor: barColors,
                                hoverBackgroundColor: hoverBarColors
                            }]
                        },
                        options: {
                            legend: {
                                display: false
                            },
                            tooltips: {
                                callbacks: {
                                    label: function (tooltipItem) {
                                        return tooltipItem.yLabel;
                                    }
                                }
                            },
                            responsive: true, // Instruct chart js to respond nicely.
                            maintainAspectRatio: false, // Add to prevent default behaviour of full-width/height 
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true
                                    }
                                }]
                            }
                        }
                    });
                }
                //Income Pie
                $(".chart1").remove();
                $(".chart2").remove();
                $(".chart3").remove();
                $("#pie").remove();
                if (window.newIncomePies != undefined) { window.newIncomePies.destroy() };
                if (pieIncome == '') {
                    $("#newIncomePie").hide();
                    $("#error5").show();
                } else {
                    $("#newIncomePie").show();
                    $("#error5").hide();
                    let pieIncomeCategories = pieIncomeCategory.split(',');
                    let pieIncomes = pieIncome.split(',');
                    let incomeColor = [];
                    let hoverIncomeColor = [];
                    for (i = 0; i < pieIncomeCategories.length; i++) {
                        incomeColor.push(getColor());
                    }
                    for (i = 0; i < pieIncomeCategories.length; i++) {
                        hoverIncomeColor.push(getColor());
                    }
                    window.newIncomePies = new Chart(newIncomePie, {
                        type: 'pie',
                        data: {
                            labels: pieIncomeCategories,
                            datasets: [{
                                data: pieIncomes,
                                backgroundColor: incomeColor,
                                hoverBackgroundColor: hoverIncomeColor
                                // Specify the data values array
                            }]
                        },
                        options: {
                            responsive: true, // Instruct chart js to respond nicely.
                            maintainAspectRatio: false, // Add to prevent default behaviour of full-width/height 
                        }
                    });
                }


                // expense pie
                $("#epie").remove();
                if (window.newExpensePies != undefined) { window.newExpensePies.destroy() };
                if (pieExpense == '') {
                    $("#newExpensePie").hide();
                    $("#error6").show();
                } else {
                    $("#newExpensePie").show();
                    $("#error6").hide();
                    let pieExpenseCategories = pieExpenseCategory.split(',');
                    let pieExpenses = pieExpense.split(',');
                    let expenseColor = [];
                    let hoverExpenseColor = [];
                    for (i = 0; i < pieExpenseCategories.length; i++) {
                        expenseColor.push(getColor());
                    }
                    for (i = 0; i < pieExpenseCategories.length; i++) {
                        hoverExpenseColor.push(getColor());
                    }

                    newExpensePies = new Chart(newExpensePie, {
                        type: 'pie',
                        data: {
                            labels: pieExpenseCategories,
                            datasets: [{
                                data: pieExpenses,
                                backgroundColor: expenseColor,
                                hoverBackgroundColor: hoverExpenseColor // Specify the data values array
                            }]
                        },
                        options: {
                            responsive: true, // Instruct chart js to respond nicely.
                            maintainAspectRatio: false, // Add to prevent default behaviour of full-width/height 
                        }
                    });
                }
            }
        });
    });
});

function checkMonthYearMatch(first_unix_timestamp, second_unix_timestamp) {

    const stringFirstDate = new Date(first_unix_timestamp * 1000);
    const targetYear = stringFirstDate.getFullYear();

    let stringSecondDate;
    if (second_unix_timestamp == undefined) {
        stringSecondDate = new Date();
    } else {
        stringSecondDate = new Date(second_unix_timestamp * 1000);
    }
    const currentYear = stringSecondDate.getFullYear();

    if (targetYear == currentYear) {
        return true;
    } else {
        return false;
    }
}

function getColor() {
    return "hsl(" + 360 * Math.random() + ',' +
        (15 + 70 * Math.random()) + '%,' +
        (55 + 10 * Math.random()) + '%)'
}
