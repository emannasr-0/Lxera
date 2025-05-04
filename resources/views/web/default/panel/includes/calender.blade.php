<style>
    .container_cal {
        max-width: 100% !important;
        margin: 0 auto;
        padding: 25px;
        background-color: #fff;
        /* background-color: #141F25; */
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        font-family: sans-serif !important;
    }

    #current-month-year {
        text-align: center;
        margin-bottom: 10px;
        color: #333;
        /* color: #fff; */
        font-size: 24px;
        margin-top: 0;
        font-family: sans-serif !important;

    }

    .row_cal {
        margin-bottom: 20px;    font-family: sans-serif !important;

    }

    .col_cal {
        padding: 0 15px;    font-family: sans-serif !important;

    }
    .d-flex_cal {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;    font-family: sans-serif !important;

    }

    .table_cal {
        width: 100%;
        border-collapse: collapse;    font-family: sans-serif !important;

    }

    .table_cal th,
    .table_cal td {
        padding: 10px !important;
        border: 1px solid #ccc;
        text-align: center;    font-family: sans-serif !important;

    }

    .table_cal th {
        background-color: #f2f2f2;    font-family: sans-serif !important;

    }

    button {
        padding: 5px 10px;
        border: 1px solid #ccc;
        background-color: #f2f2f2;
        cursor: pointer;
        color: #333;
        transition: background-color 0.3s, color 0.3s;
        border-radius: 3px;    font-family: sans-serif !important;

    }

    button:hover {
        background-color: #e6e6e6;    font-family: sans-serif !important;

    }
    h5{    font-family: sans-serif !important;
}
    button{    font-family: sans-serif !important;
}
    ._cal{    font-family: sans-serif !important;
}
.modal-header .close {
    padding: 0 !important;
    margin: 0rem 0rem auto !important;
}
.btn-secondary:active, .btn-secondary:focus, .btn-secondary:hover, .btn-secondary:not(:disabled):not(.disabled):active {
    color: #333 !important;
    background-color: #5F2B80 !important;
    border-color: #5F2B80 !important;
    box-shadow: #5F2B80 !important;
    transition: all 0.3s !important;
}
    .course-title{
            cursor: pointer !important;
    }
    td.event-bg {
    background-color: #c14b93 !important;
    color: #fff !important;
    border-radius: 50%;  /* Makes it a circle */
    width: 40px;  /* Adjust as needed */
    height: 40px; /* Same as width to maintain a circle */
    text-align: center; /* Centers text inside */
    display: flex;
    align-items: center;
    justify-content: center;
    margin: auto;
}

    @media (max-width: 792px) {
        .col_cal .table-container .table {
            width: 100% !important;
        }
    }

    @media(max-width: 600px) {
        .col_cal {
            padding: 0;
        }
        ._cal { 
            font-size:8px;
            
        }
        .table tr {
            display: flex;
            justify-content: space-between;
            align-items:center;
        }
        .table th {
            padding: 0;
            /* padding-bottom:10px; */
            font-size:10px;
        }
        .table td {
            font-size:12px;
            padding: 10px !important;
        }
        button {
            font-size: 12px;
        }
        .course-title {
            font-size: 12px;
        }
        #prev-month, #next-month {
            padding:8px !important;
            width: fit-content;
            height: fit-content;
            font-size: 12px !important;
        }
        .event-bg {
            margin:0 !important;
            width: 30px;
            height: 30px;
        }
    }

</style>

<div class="container_cal rounded-sm shadow border">
    <h1 id="current-month-year">Calendar</h1>
    <div class="row_cal">
        <div class="col_cal">
            <div class="d-flex_cal justify-content-between_cal my-15 my-lg-0">
                <button class="btn btn-primary" id="prev-month">{{ trans('panel.previous_month') }}</button>
                <button class="btn btn-primary" id="next-month">{{ trans('panel.next_month') }}</button>
            </div>
            <div class="table-container">
            <table class="table ">
                <thead class="_cal">
                    <tr class="_cal">
                        <th class="_cal">{{ trans('panel.sunday') }}</th>
                        <th class="_cal">{{ trans('panel.monday') }}</th>
                        <th class="_cal">{{ trans('panel.tuesday') }}</th>
                        <th class="_cal">{{ trans('panel.wednesday') }}</th>
                        <th class="_cal">{{ trans('panel.thursday') }}</th>
                        <th class="_cal">{{ trans('panel.friday') }}</th>
                        <th class="_cal">{{ trans('panel.saturday') }}</th>
                    </tr>
                </thead>
                <tbody id="calendar-body"></tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<!-- Popup -->
<div class="modal fade" id="bundlePopup" tabindex="-1" role="dialog" aria-labelledby="bundlePopupLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content bg-secondary-acadima">
            <div class="modal-header">
                <h5 class="modal-title" id="bundlePopupLabel">{{ trans('panel.courses_on_this_day') }} </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="bundleStartDate"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-acadima-primary  " data-dismiss="modal">{{ trans('panel.close') }}</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    function dateTimeFormat(timestamp) {
        const date = new Date(timestamp * 1000);
        const year = date.getFullYear();
        const month = ('0' + (date.getMonth() + 1)).slice(-2);
        const day = ('0' + date.getDate()).slice(-2);

        return `${year}-${month}-${day}`;
    }
    $(document).ready(function() {
        const calendarBody = $('#calendar-body');
        const bundlePopup = $('#bundlePopup');
        const currentMonthYear = $('#current-month-year');

        const bundles = @json($webinars);
        let currentDate = new Date();
        let currentYear = currentDate.getFullYear();
        let currentMonth = currentDate.getMonth();

        updateCalendar();

        $('#prev-month').click(function() {
            if (currentMonth === 0) {
                currentMonth = 11;
                currentYear--;
            } else {
                currentMonth--;
            }
            updateCalendar();
        });

        $('#next-month').click(function() {
            if (currentMonth === 11) {
                currentMonth = 0;
                currentYear++;
            } else {
                currentMonth++;
            }
            updateCalendar();
        });



        function updateCalendar() {
    currentMonthYear.text(`${currentYear}-${currentMonth + 1}`);
    calendarBody.empty();

    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
    const firstDayOfWeek = new Date(currentYear, currentMonth, 1).getDay();

    let currentDay = 1;

    for (let i = 0; currentDay <= daysInMonth; i++) {
        const row = $('<tr>');
        for (let j = 0; j < 7; j++) {
            if ((i === 0 && j < firstDayOfWeek) || currentDay > daysInMonth) {
                row.append('<td></td>');
            } else {
                const date = new Date(currentYear, currentMonth, currentDay);
                const formattedDate = formatDate(date);
                const allBundles = bundles.filter(bundle => formatDate(new Date(bundle.start_date * 1000)) === formattedDate);
                
                // Apply event-bg ONLY if the day has bundles
                const dayClass = (allBundles.length > 0) ? 'day-with-bundle event-bg' : '';

                let details = "";
                for (const bundle of allBundles) {
                    details = "lectures";
                }

                row.append(`<td class=" ${dayClass}" data-date="${formattedDate}">
                    ${currentDay}
                    <p class='course-title'></p>
                </td>`);
                currentDay++;
            }
        }
        calendarBody.append(row);
    }

    // Click event for bundle days
    $('.day-with-bundle').off('click').on('click', function() {
        const date = $(this).data('date');
        const bundlesData = bundles.filter(bundle => formatDate(new Date(bundle.start_date * 1000)) === date);

        let text = "";
        for (const bundle of bundlesData) {
            text += bundle.title + "<br>";
        }
        $('#bundleStartDate').html(`${text}`);
        bundlePopup.modal('show');
    });
}

        function formatDate(date) {
            const date2 = new Date(date);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const day = String(date.getDate()).padStart(2, "0");
            const formattedDate = `${year}-${month}-${day}`;

            return formattedDate;
        }
    });
</script>
