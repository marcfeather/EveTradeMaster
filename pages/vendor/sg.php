
<select id="ddYear">
    <option name="year" value="1997">1997</option>
    <option name="year" value="1998">1998</option>
    <option name="year" value="1999">1999</option>
</select>
<table id="Table1">
    <tr>
        <th>Year</th>
        <th>Version</th>
        <th>Name</th>
    </tr>
    <tr class="clickable">
        <td id="Td1" class="AR AR_1999">1999</td>
        <td>Some version Number</td>
        <td>Some Name</td>
    </tr>
    <tr class="clickable">
        <td class="AR_1997">1997</td>
        <td>Some version Number</td>
        <td>Some Name</td>
    </tr>
    <tr class="clickable">
        <td class="AR AR_1999">1999</td>
        <td>Some version Number</td>
        <td>Some Name</td>
    </tr>
    <tr class="clickable">
        <td class="AR AR_1998">1998</td>
        <td>Some version Number</td>
        <td>Some Name</td>
    </tr>
</table>
<script>
     $("#ddYear").on('change', function () {
        var year = $("#ddYear").val();
        $('tr').show();

        $("td.AR").each(function (index, tdAR) {
            if ($(tdAR).hasClass("AR_" + year)) {
                $(tdAR).parent('tr').hide();
            }
        });
    });
    </script>