<div class="container-fluid py-4">
    <div class="row">
        <div class="col-sm-4">
          @include('dasboard.total_sales')
        </div>
        <div class="col-sm-4 mt-sm-0 mt-4">
          @include('dasboard.total_item')
           
        </div>
        <div class="col-sm-4 mt-sm-0 mt-4">
            @include('dasboard.total_customer')
        </div>
    </div>
    <div class="row mt-4">
        @include('dasboard.chart_mount')

        <div class="col-lg-8 col-sm-6 mt-sm-0 mt-4">
           @include('dasboard.top_seller')
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header pb-0 p-3">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-0">Sales by Age</h6>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="chart">
                        <canvas id="chart-bar" class="chart-canvas" height="340"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mt-lg-0 mt-4">
            <div class="card">
                <div class="card-header pb-0 p-3">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-0">Sales by Country</h6>
                    </div>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush list my--3">
                        <li class="list-group-item px-0 border-0">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <img src="../../assets/img/icons/flags/US.png" alt="Country flag">
                                </div>
                                <div class="col">
                                    <p class="text-xs font-weight-bold mb-0">Country:</p>
                                    <h6 class="text-sm font-weight-normal mb-0">United States</h6>
                                </div>
                                <div class="col text-center">
                                    <p class="text-xs font-weight-bold mb-0">Sales:</p>
                                    <h6 class="text-sm font-weight-normal mb-0">2500</h6>
                                </div>
                                <div class="col text-center">
                                    <p class="text-xs font-weight-bold mb-0">Bounce:</p>
                                    <h6 class="text-sm font-weight-normal mb-0">29.9%</h6>
                                </div>
                            </div>
                            <hr class="horizontal dark mt-3 mb-1">
                        </li>
                        <li class="list-group-item px-0 border-0">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <img src="../../assets/img/icons/flags/DE.png" alt="Country flag">
                                </div>
                                <div class="col">
                                    <p class="text-xs font-weight-bold mb-0">Country:</p>
                                    <h6 class="text-sm font-weight-normal mb-0">Germany</h6>
                                </div>
                                <div class="col text-center">
                                    <p class="text-xs font-weight-bold mb-0">Sales:</p>
                                    <h6 class="text-sm font-weight-normal mb-0">3.900</h6>
                                </div>
                                <div class="col text-center">
                                    <p class="text-xs font-weight-bold mb-0">Bounce:</p>
                                    <h6 class="text-sm font-weight-normal mb-0">40.22%</h6>
                                </div>
                            </div>
                            <hr class="horizontal dark mt-3 mb-1">
                        </li>
                        <li class="list-group-item px-0 border-0">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <img src="../../assets/img/icons/flags/GB.png" alt="Country flag">
                                </div>
                                <div class="col">
                                    <p class="text-xs font-weight-bold mb-0">Country:</p>
                                    <h6 class="text-sm font-weight-normal mb-0">Great Britain</h6>
                                </div>
                                <div class="col text-center">
                                    <p class="text-xs font-weight-bold mb-0">Sales:</p>
                                    <h6 class="text-sm font-weight-normal mb-0">1.400</h6>
                                </div>
                                <div class="col text-center">
                                    <p class="text-xs font-weight-bold mb-0">Bounce:</p>
                                    <h6 class="text-sm font-weight-normal mb-0">23.44%</h6>
                                </div>
                            </div>
                            <hr class="horizontal dark mt-3 mb-1">
                        </li>
                        <li class="list-group-item px-0 border-0">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <img src="../../assets/img/icons/flags/BR.png" alt="Country flag">
                                </div>
                                <div class="col">
                                    <p class="text-xs font-weight-bold mb-0">Country:</p>
                                    <h6 class="text-sm font-weight-normal mb-0">Brasil</h6>
                                </div>
                                <div class="col text-center">
                                    <p class="text-xs font-weight-bold mb-0">Sales:</p>
                                    <h6 class="text-sm font-weight-normal mb-0">562</h6>
                                </div>
                                <div class="col text-center">
                                    <p class="text-xs font-weight-bold mb-0">Bounce:</p>
                                    <h6 class="text-sm font-weight-normal mb-0">32.14%</h6>
                                </div>
                            </div>
                            <hr class="horizontal dark mt-3 mb-1">
                        </li>
                        <li class="list-group-item px-0 border-0">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <img src="../../assets/img/icons/flags/AU.png" alt="Country flag">
                                </div>
                                <div class="col">
                                    <p class="text-xs font-weight-bold mb-0">Country:</p>
                                    <h6 class="text-sm font-weight-normal mb-0">Australia</h6>
                                </div>
                                <div class="col text-center">
                                    <p class="text-xs font-weight-bold mb-0">Sales:</p>
                                    <h6 class="text-sm font-weight-normal mb-0">400</h6>
                                </div>
                                <div class="col text-center">
                                    <p class="text-xs font-weight-bold mb-0">Bounce:</p>
                                    <h6 class="text-sm font-weight-normal mb-0">56.83%</h6>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Top Selling Products</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Product</th>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Value</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Ads Spent</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Refunds</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div>
                                                <img src="https://raw.githubusercontent.com/creativetimofficial/public-assets/master/soft-ui-design-system/assets/img/ecommerce/blue-shoe.jpg"
                                                    class="avatar me-3" alt="image">
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">Nike v22 Running</h6>
                                                <p class="text-sm font-weight-normal text-secondary mb-0">
                                                    <span class="text-success">8.232</span> orders</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-normal mb-0">$130.992</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <p class="text-sm font-weight-normal mb-0">$9.500</p>
                                    </td>
                                    <td class="align-middle text-end">
                                        <div
                                            class="d-flex px-3 py-1 justify-content-center align-items-center">
                                            <p class="text-sm font-weight-normal mb-0">13</p>
                                            <i class="ni ni-bold-down text-sm ms-1 text-success"></i>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div>
                                                <img src="https://raw.githubusercontent.com/creativetimofficial/public-assets/master/soft-ui-design-system/assets/img/ecommerce/black-mug.jpg"
                                                    class="avatar me-3" alt="image">
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">Business Kit (Mug + Notebook)</h6>
                                                <p class="text-sm font-weight-normal text-secondary mb-0">
                                                    <span class="text-success">12.821</span> orders</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-normal mb-0">$80.250</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <p class="text-sm font-weight-normal mb-0">$4.200</p>
                                    </td>
                                    <td class="align-middle text-end">
                                        <div
                                            class="d-flex px-3 py-1 justify-content-center align-items-center">
                                            <p class="text-sm font-weight-normal mb-0">40</p>
                                            <i class="ni ni-bold-down text-sm ms-1 text-success"></i>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div>
                                                <img src="https://raw.githubusercontent.com/creativetimofficial/public-assets/master/soft-ui-design-system/assets/img/ecommerce/black-chair.jpg"
                                                    class="avatar me-3" alt="image">
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">Black Chair</h6>
                                                <p class="text-sm font-weight-normal text-secondary mb-0">
                                                    <span class="text-success">2.421</span> orders</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-normal mb-0">$40.600</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <p class="text-sm font-weight-normal mb-0">$9.430</p>
                                    </td>
                                    <td class="align-middle text-end">
                                        <div
                                            class="d-flex px-3 py-1 justify-content-center align-items-center">
                                            <p class="text-sm font-weight-normal mb-0">54</p>
                                            <i class="ni ni-bold-up text-sm ms-1 text-danger"></i>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div>
                                                <img src="https://raw.githubusercontent.com/creativetimofficial/public-assets/master/soft-ui-design-system/assets/img/ecommerce/bang-sound.jpg"
                                                    class="avatar me-3" alt="image">
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">Wireless Charger</h6>
                                                <p class="text-sm font-weight-normal text-secondary mb-0">
                                                    <span class="text-success">5.921</span> orders</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-normal mb-0">$91.300</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <p class="text-sm font-weight-normal mb-0">$7.364</p>
                                    </td>
                                    <td class="align-middle text-end">
                                        <div
                                            class="d-flex px-3 py-1 justify-content-center align-items-center">
                                            <p class="text-sm font-weight-normal mb-0">5</p>
                                            <i class="ni ni-bold-down text-sm ms-1 text-success"></i>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div>
                                                <img src="https://raw.githubusercontent.com/creativetimofficial/public-assets/master/soft-ui-design-system/assets/img/ecommerce/photo-tools.jpg"
                                                    class="avatar me-3" alt="image">
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">Mountain Trip Kit (Camera + Backpack)
                                                </h6>
                                                <p class="text-sm font-weight-normal text-secondary mb-0">
                                                    <span class="text-success">921</span> orders</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-normal mb-0">$140.925</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <p class="text-sm font-weight-normal mb-0">$20.531</p>
                                    </td>
                                    <td class="align-middle text-end">
                                        <div
                                            class="d-flex px-3 py-1 justify-content-center align-items-center">
                                            <p class="text-sm font-weight-normal mb-0">121</p>
                                            <i class="ni ni-bold-up text-sm ms-1 text-danger"></i>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer py-4  ">
        <div class="container-fluid">
            <div class="row align-items-center justify-content-lg-between">
                <div class="col-lg-6 mb-lg-0 mb-4">
                    <div class="copyright text-center text-sm text-muted text-lg-start">
                        ©
                        <script>
                            document.write(new Date().getFullYear())
                        </script>,
                        made with <i class="fa fa-heart"></i> by
                        <a href="https://www.creative-tim.com" class="font-weight-bold"
                            target="_blank">Creative Tim</a>
                        for a better web.
                    </div>
                </div>
                <div class="col-lg-6">
                    <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                        <li class="nav-item">
                            <a href="https://www.creative-tim.com" class="nav-link text-muted"
                                target="_blank">Creative Tim</a>
                        </li>
                        <li class="nav-item">
                            <a href="https://www.creative-tim.com/presentation" class="nav-link text-muted"
                                target="_blank">About Us</a>
                        </li>
                        <li class="nav-item">
                            <a href="https://www.creative-tim.com/blog" class="nav-link text-muted"
                                target="_blank">Blog</a>
                        </li>
                        <li class="nav-item">
                            <a href="https://www.creative-tim.com/license" class="nav-link pe-0 text-muted"
                                target="_blank">License</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
</div>