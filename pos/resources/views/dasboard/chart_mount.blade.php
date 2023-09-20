<style>
    #chartdiv{
        width: 100%;
        height: 300px;
    }
</style>
<script>
    am5.ready(function() {
    var root = am5.Root.new("chartdiv");
    root.setThemes([
      am5themes_Animated.new(root)
    ]);
    var chart = root.container.children.push(
      am5percent.PieChart.new(root, {
        endAngle: 270
      })
    );
    var series = chart.series.push(
      am5percent.PieSeries.new(root, {
        valueField: "value",
        categoryField: "category",
        endAngle: 270
      })
    );
    
    series.states.create("hidden", {
      endAngle: -90
    });
    
    // Set data
    // https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/#Setting_data
    series.data.setAll([{
      category: "Lithuania",
      value: 501.9
    }, {
      category: "Czechia",
      value: 301.9
    }, {
      category: "Ireland",
      value: 201.1
    }, {
      category: "Germany",
      value: 165.8
    }, {
      category: "Australia",
      value: 139.9
    }, {
      category: "Austria",
      value: 128.3
    }, {
      category: "UK",
      value: 99
    }]);
    
    series.appear(1000, 100);
    
    }); 
    </script>
<div class="col-lg-4 col-sm-6">
    <div class="card h-100">
        <div class="card-header pb-0 p-3">
            <div class="d-flex justify-content-between">
                <h6 class="mb-0">Channels</h6>
                <button type="button"
                    class="btn btn-icon-only btn-rounded btn-outline-secondary mb-0 ms-2 btn-sm d-flex align-items-center justify-content-center"
                    data-bs-toggle="tooltip" data-bs-placement="bottom" title
                    data-bs-original-title="See traffic channels">
                    <i class="material-icons text-sm">priority_high</i>
                </button>
            </div>
        </div>
        <div class="card-body pb-0 p-3 mt-4">
            <div class="row">
                <div class="col-10 text-start">
                    <div class="chart">
                         <div id="chartdiv"></div>
                    </div>
                </div>
               
            </div>
        </div>
        <div class="card-footer pt-0 pb-0 p-3 d-flex align-items-center">
            <div class="w-60">
                <p class="text-sm">
                    More than <b>1,200,000</b> sales are made using referral marketing, and
                    <b>700,000</b> are from social media.
                </p>
            </div>
            <div class="w-40 text-end">
                <a class="btn bg-light mb-0 text-end" href="javascript:;">Read more</a>
            </div>
        </div>
    </div>
</div>