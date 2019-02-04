const datepickerInputElement = $("#datepicker");
const timeslotsElement = $("#timeslots");
const categoryInput = $("#categoryInput");
let timeslots;

function updateCalendar() {
    if (datepickerInputElement.hasClass("hasTimeslots")) {
        datepickerElement.removeClass("hidden");
        let category = $("#categoryInput option:selected").val();

        // Clear selected dates and timeslots
        datepicker.clear();
        timeslotsElement.empty();

        $.ajax({url: `available_slots.php?category=${category}`, success: function(result){
            timeslots = result;
            datepicker.update({
                // Check if calendar date is in available timeslots. If not, disable cell
                onRenderCell: function (date, cellType) {
                    if (cellType === "day") {
                        let fullYear  = date.getFullYear(),
                            fullMonth = (date.getMonth() + 1) < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1,
                            fullDay   = date.getDate() < 10 ? '0' + date.getDate() : date.getDate(),
                            fullDate = (`${fullYear}-${fullMonth}-${fullDay}`),
                            isDisabled = timeslots.find(x => x.date.from.includes(`${fullDate}`));

                        return {
                            disabled: isDisabled === undefined
                        };
                    }
                }
            });
        }});
    }
}

// Initialization of datepicker
let timeslotsThisDay;
let timeslotFrom;
let timeslotUntil;
let timeslotHTML;
datepickerInputElement.datepicker({
    language: "nl",
    inline: true,
    dateFormat: "yyyy-mm-dd",
    minDate: new Date(),
    // On Select add available timeslots to page
    onSelect: function (formattedDate) {
        if (datepickerInputElement.hasClass("hasTimeslots")) {
            // If date has been selected, else nothing is selected
            if (formattedDate) {
                // Empty timeslots
                timeslotsElement.empty();

                timeslotsThisDay = timeslots.filter(x => x.date.from.includes(`${formattedDate}`));
                timeslotsThisDay.forEach(function (timeslot) {
                    timeslotFrom = timeslot['time']['from'];
                    timeslotUntil = timeslot['time']['until'];
                    timeslotHTML = `
                    <li class="timeslot">
                        ${timeslotFrom} - ${timeslotUntil}
                        <input class="from_time" type="text" name="from_time" value="${timeslotFrom}" disabled hidden>
                        <input class="until_time" type="text" name="until_time" value="${timeslotUntil}" disabled hidden>
                    </li>`;
                    // Add this timeslot to the list
                    timeslotsElement.append(timeslotHTML)
                });
                $(".timeslot").click(function () {
                    $(".from_time, .until_time").attr("disabled", "");
                    $("#timeslots .timeslot").removeClass("selected");
                    $(this).addClass("selected");
                    $(".from_time", this).removeAttr("disabled")
                    $(".until_time", this).removeAttr("disabled")
                });
            } else {
                timeslotsElement.empty();
            }
        }
    }
});
let datepicker = datepickerInputElement.data("datepicker");
const datepickerElement = $(".datepicker");
datepickerElement.addClass("hidden");

// If date field is already filled show calendar and set to correct date
if (datepickerInputElement.attr("value")) {
    datepickerElement.removeClass("hidden");
    let newDate= new Date(datepickerInputElement.attr("value"));
    datepicker.selectDate(newDate);
}

// When category changes
categoryInput.change(function() {
    updateCalendar();
});