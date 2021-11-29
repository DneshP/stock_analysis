<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.js" integrity="sha512-OmBbzhZ6lgh87tQFDVBHtwfi6MS9raGmNvUNTjDIBb/cgv707v9OuBVpsN6tVVTLOehRFns+o14Nd0/If0lE/A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    const form = new Vue({
        el: '#stockDataForm',
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
                chunkSize: 1000 * 1024
            }
        },
        methods:{
            submit() {
                if (!this.isFormValid()) {
                    this.registerAndClearNotification('formInvalid', 'Please check your inputs.')
                } else {
                    const fd = new FormData();
                    fd.append('stockData', this.stockData);
                    fd.append('stock', this.selectedStock);
                    fd.append('startDate', this.startDate);
                    fd.append('endDate', this.endDate);
                }
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
            parseCSV(text, hasTags = false){
                const delimiter = ',';
                const rowDelimiter = '\n';
                if (hasTags) {
                    this.tags = text.slice(0, text.indexOf('\n')).split(delimiter)
                }
                const rows = text.slice(text.indexOf('\n')).split(rowDelimiter);
                const data = [];
                const stocks = [];
                this.tags.map((tag, tagIndex) => {
                    rows.forEach((row, index) => {
                        const rowData = row.split(delimiter);
                        if(tag === 'stock_name') {
                           rowData[tagIndex] = rowData[tagIndex] + index;
                        }
                        if (tag === 'stock_name' && !stocks.includes(rowData[tagIndex])) {
                            stocks.push(rowData[tagIndex]);
                            this.stockList.push(rowData[tagIndex]);
                        }
                        if (!data[index]) {
                            data.push({[tag]: rowData[tagIndex]});
                        } else {
                            data[index][tag] = rowData[tagIndex];
                        }
                    });
                });
            },
            clearFileData() {
                const input = document.getElementById('stockData');
                input.value = '';
                this.registerAndClearNotification(input.id, 'File removed', 'success');
                input.classList.add('is-invalid');
                this.stockData = '';
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
                    if (this.stockData.size > 26214400) {
                        this.chunkSize = 1000 * 300;
                    }
                    const response = await this.readCSV(0, true);
                    if (response.status) {
                        this.isUploaded = !this.isUploaded;
                        this.isProcessing = !this.isProcessing;
                        this.toggleClass(input, 'remove', 'is-invalid');
                    }
                } else {
                    this.stockData = '';
                    this.fields.stockData = false;
                    this.toggleClass(event.target, 'add', 'is-invalid');
                }
            },
            async readCSV(start, tags = false) {
                const reader = new FileReader();
                return new Promise(((resolve, reject) => {
                    const nextSlice = start + this.chunkSize + 1;
                    const blob = this.stockData.slice(start, nextSlice);
                    reader.onloadend = (event) => {
                        this.parseCSV(event.target.result, tags);
                        if (nextSlice < this.stockData.size) {
                            this.readCSV(nextSlice);
                        }else {
                            resolve({status: true});
                        }
                    }
                    reader.readAsBinaryString( blob );
                }));
            },
            validatePickAStock(event) {
                const element = event.target;
                if (this.stockList.length === 0) {
                    this.registerAndClearNotification(element.id, 'Please upload the stock data first');
                } else {
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
                if (this.stockList.length === 0) {
                    const id = event.target.id;
                    this.registerAndClearNotification(id, 'Please upload the stock data first');
                }
            },
            validateDate(event) {
                const element = event.target;
                const isStart = element.id.includes('start');
                if (!this.hasValue(element.id)) {
                    this.registerAndClearNotification(element.id + 'isEmpty', `${isStart ? 'Start ' : 'End '}` + 'Date field is required')
                    this.toggleClass(element, 'add', 'is-invalid');
                    this.fields[element.id] = false;
                } else {
                    if (!this.isValidDate(element.id)) {
                        this.registerAndClearNotification(element.id + 'isNotValid', `${isStart ? 'Start ' : 'End '}` + 'Date field is invalid');
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
                if (method == 'add' && !hasClass) {
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
