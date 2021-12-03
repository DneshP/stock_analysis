<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.js" integrity="sha512-OmBbzhZ6lgh87tQFDVBHtwfi6MS9raGmNvUNTjDIBb/cgv707v9OuBVpsN6tVVTLOehRFns+o14Nd0/If0lE/A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    const stockAnalysis = new Vue({
        el: '#stockDataAnalysis',
        data() {
            return {
                stockData: '',
                selectedStock: '',
                startDate: '',
                endDate: '',
                stockList: [],
                isProcessing: false,
                isUploaded: false,
                currentError: {},
                fields: {
                    stockData: false,
                    selectedStock: false,
                    startDate: false,
                    endDate: false
                },
                tags: [],
                stocks: [],
                allStock:[],
                chunkSize: 1000 * 1024,
                showResults: false,
                analysis: {}
            }
        },
        computed: {
          isProfit() {
              return this.analysis?.buySellDates?.profit > 0;
          }
        },
        methods:{
            async submit() {
                if (!this.isFormValid()) {
                    this.registerAndClearNotification('formInvalid', 'Please check your inputs.')
                } else {
                    const fd = new FormData();
                    const url = "<?=BASE_URL?>" + '/analyseStockData';
                    fd.append('stock', this.selectedStock.toLowerCase());
                    fd.append('startDate', this.startDate);
                    fd.append('endDate', this.endDate);
                    const config = {
                            method: 'POST',
                            mode: 'cors',
                            cache: 'no-cache',
                            credentials: 'same-origin',
                            body: fd
                    };
                    const response = await this.postData(url, config);
                    let message;
                    let type = 'error';
                    if (response.status) {
                        this.clearFileData(true);
                        this.analysis = response.data;
                        this.showResults = !this.showResults;
                        return;
                    } else {
                        message = response.data ?? 'Internal server error';
                    }
                    this.notify(message, type);
                }
            },
            refresh() {
                this.stockData = '';
                this.selectedStock = '';
                this.startDate = '';
                this.endDate = '';
                this.showResults = !this.showResults;
            },
            isFormValid() {
                const fields = JSON.parse(JSON.stringify(this.fields));
                let isValid = true;
                for (const [key,value] of Object.entries(fields)) {
                    if (!value) {
                        isValid = false;
                        this.toggleClass(document.getElementById(key), 'add', 'is-invalid');
                    }
                }
                return isValid;
            },
            clearFileData(hide = false) {
                const input = document.getElementById('stockData');
                input.value = '';
                if (!hide) {
                    this.notify('File removed', 'success');
                }
                input.classList.add('is-invalid');
                this.stockData = '';
                this.stockList = [];
                this.allStock = [];
                this.isUploaded = !this.isUploaded;
            },
            async uploadStockData(event) {
                this.stockList = [];
                const input = document.getElementById('stockData');
                const pattern = /^([a-zA-Z0-9\s_\\.\-:(0-9)])+(.csv)$/;
                const file = input.files[0];
                if (file) {
                    this.isProcessing = !this.isProcessing;
                    if (!pattern.test(file.name)) {
                        this.notify('File Not Accepted. Only CSV format is accepted');
                        this.isProcessing = !this.isProcessing;
                        return;
                    }
                    this.stockData = file;
                    await this.readCSV(0, true, true);
                } else {
                    this.stockData = '';
                    this.fields.stockData = false;
                    this.toggleClass(event.target, 'add', 'is-invalid');
                }
            },
            async readCSV(start, tags = false, fresh = false) {
                const reader = new FileReader();
                return new Promise(((resolve) => {
                    let nextSlice = start + this.chunkSize + 1;
                    let blob = this.stockData.slice(start, nextSlice);
                    if (nextSlice > this.stockData.size) {
                        const lastSlice = nextSlice - this.stockData.size;
                        blob = this.stockData.slice(start, lastSlice);
                    }
                    reader.onloadend = async (event) => {
                        const config = {
                            method: 'POST',
                            mode: 'cors',
                            cache: 'no-cache',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: JSON.stringify({status: 'on', chunk: event.target.result, hasTags: tags, fresh})
                        }
                        const response = await this.postData('<?=BASE_URL?>' + '/streamStockData', config);
                        if (response.status) {
                            if (nextSlice < this.stockData.size) {
                                resolve({status: true});
                                await this.readCSV(nextSlice);
                            }else {
                                const config = {
                                    method: 'POST',
                                    mode: 'cors',
                                    cache: 'no-cache',
                                    credentials: 'same-origin',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({status: 'done'})
                                }
                                const response = await this.postData('<?=BASE_URL?>' + '/streamStockData', config);
                                if (response.status) {
                                    this.stockList = response.data.map(stock => stock.toUpperCase());
                                    this.isUploaded = !this.isUploaded;
                                    this.isProcessing = !this.isProcessing;
                                    const input = document.getElementById('stockData');
                                    this.toggleClass(input, 'remove', 'is-invalid');
                                    this.fields.stockData = true;
                                    resolve({status: true});
                                } else {
                                    this.notify('Failed to process data');
                                    this.isUploaded = !this.isUploaded;
                                    this.isProcessing = !this.isProcessing;
                                    const input = document.getElementById('stockData');
                                    input.value = '';
                                    this.toggleClass(input, 'remove', 'is-invalid');
                                    resolve({status: false});
                                }

                            }
                        } else {
                            this.isProcessing = !this.isProcessing;
                            console.log(this.isUploaded);
                            console.log(response);
                            this.notify(response.data);
                        }
                    }
                    reader.readAsDataURL( blob );
                }));
            },
            async postData(url, config) {
                const response = await fetch(url, config);
                return response.json();
            },
            validatePickAStock(event) {
                const element = event.target;
                if (!this.isProcessing) {
                    if (!this.hasValue(element.id)) {
                        this.registerAndClearNotification(element.id, 'Please pick a stock');
                        this.toggleClass(element, 'add', 'is-invalid');
                        this.fields.selectedStock = false;
                    }else {
                        this.fields.selectedStock = true;
                        this.toggleClass(element, 'remove', 'is-invalid');
                    }
                }
            },
            validateStockList(event) {
                const id = event.target.id;
                let message;
                if (this.isProcessing) {
                    message = 'Please wait while the file is being processed.'
                }
                if (!this.isUploaded && !this.isProcessing) {
                    message = 'Please upload the stock data first';
                }
                if (message) {
                    this.registerAndClearNotification(id, message);
                }
            },
            validateDate(event) {
                const element = event.target;
                const isStart = element.id.includes('start');
                if (!this.hasValue(element.id)) {
                    this.registerAndClearNotification(element.id + 'isEmpty',
                        `${isStart ? 'Start ' : 'End '}` + 'Date field is required')
                    this.toggleClass(element, 'add', 'is-invalid');
                    this.fields[element.id] = false;
                } else {
                    if (!this.isValidDate(element.id)) {
                        this.registerAndClearNotification(element.id + 'isNotValid',
                            `${isStart ? 'Start ' : 'End '}` + 'Date field is invalid');
                        this.toggleClass(element, 'add', 'is-invalid');
                        this.fields[element.id] = false;
                    } else {
                        this.toggleClass(element, 'remove', 'is-invalid');
                        this.fields[element.id] = true;
                    }
                }
            },
            hasValue(field) {
                return this[field] !== undefined && this[field] !== '';
            },
            isValidDate(field) {
                const date = this[field];
                return !(isNaN(new Date(date).getDate()));
            },
            isValidStock(field) {
                return this.stockList.includes(field);
            },
            toggleClass(element, method, className) {
                const hasClass = element.classList.contains(className);
                if (method === 'add' && !hasClass) {
                    element.classList.add(className);
                } else if (method === 'remove' && hasClass) {
                    element.classList.remove(className);
                }
            },
            registerAndClearNotification(id, message){
                if (!this.currentError[id]) {
                    this.currentError[id] = true;
                    this.notify(message);
                    setTimeout(function (that) {
                        delete that.currentError[id];
                    }, 4000, this);
                }
            },
            notify(title, type='error') {
                iziToast[type]({
                    theme: 'light',
                    title,
                    position: 'topRight',
                });
            }
        }
    });
</script>
</body>
</html>
