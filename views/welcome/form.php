<style>
    body{
        background: linear-gradient(#ba416c, #2a37b6);
    }
    [v-cloak]{
        display: none;
    }
    .loader,
    .loader:before,
    .loader:after {
        border-radius: 50%;
        width: 2.5em;
        height: 2.5em;
        -webkit-animation-fill-mode: both;
        animation-fill-mode: both;
        -webkit-animation: load7 1.8s infinite ease-in-out;
        animation: load7 1.8s infinite ease-in-out;
    }
    .loading {
        margin-bottom: 3rem;
    }
    .loader {
        z-index: 1;
        color: #ffffff;
        font-size: 10px;
        margin: 0 auto;
        position: relative;
        text-indent: -9999em;
        -webkit-transform: translateZ(0);
        -ms-transform: translateZ(0);
        transform: translateZ(0);
        -webkit-animation-delay: -0.16s;
        animation-delay: -0.16s;
    }
    .loader:before,
    .loader:after {
        content: 'Loading...';
        position: absolute;
        top: 0;
    }
    .loader:before {
        left: -3.5em;
        -webkit-animation-delay: -0.32s;
        animation-delay: -0.32s;
    }
    .loader:after {
        left: 3.5em;
    }
    @-webkit-keyframes load7 {
        0%,
        80%,
        100% {
            box-shadow: 0 2.5em 0 -1.3em;
        }
        40% {
            box-shadow: 0 2.5em 0 0;
        }
    }
    @keyframes load7 {
        0%,
        80%,
        100% {
            box-shadow: 0 2.5em 0 -1.3em;
        }
        40% {
            box-shadow: 0 2.5em 0 0;
        }
    }
    .deleteIcon{
        position: relative;
        top: 32px;
    }

</style>
<div class="container-fluid form-container bg-transparent">
    <form id="stockDataForm" v-cloak>
        <div :class="{'loader':isProcessing}"></div>
        <div class="row">
            <div class="mb-3" :class="{'col-10' : isUploaded}">
                <label for="stockData" class="form-label">Stock Data</label>
                <input
                        class="form-control is-invalid"
                        type="file"
                        id="stockData"
                        aria-describedby="fileHelp"
                        accept="text/csv"
                        @change="uploadStockData"
                        :disabled="isProcessing"
                >
                <div id="fileHelp" class="invalid-feedback" style="color: black">Please upload stock data file
                    to begin. Accepted File format is csv.</div>
            </div>
            <div class="col-2" v-if="isUploaded">
                <span class="deleteIcon" @click="clearFileData">
                    <svg xmlns="http://www.w3.org/2000/svg" height="35px" viewBox="0 0 24 24" width="35px"
                         fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path
                        d="M16 9v10H8V9h8m-1.5-6h-5l-1 1H5v2h14V4h-3.5l-1-1zM18 7H6v12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7z"/>
                    </svg>
                </span>
            </div>
        </div>
        <div class="mb-3">
            <div class="form-floating">
                <select
                        class="form-select"
                        id="selectedStock"
                        aria-label="Stock"
                        v-model="selectedStock"
                        @blur="validatePickAStock"
                        @click="validateStockList">
                    <option v-if="stockList.length>0"
                            v-for="stock in stockList">{{ stock }}</option>
                </select>
                <label for="stock">Pick a stock</label>
            </div>
        </div>
            <div class="row">
                <div class="col form-floating mb-3">
                    <input
                            type="date"
                            id="startDate"
                            class="form-control"
                            placeholder="Start date"
                            @blur="validateDate"
                            v-model="startDate"
                    >
                    <label for="startDate">Start Date</label>
                </div>
                <div class="col form-floating mb-3">
                    <input
                            type="date"
                            id="endDate"
                            class="form-control"
                            placeholder="End date"
                            aria-label="End Date"
                            @blur="validateDate"
                            v-model="endDate"
                    >
                    <label for="endDate">End Date</label>
                </div>
            </div>
        <div class="text-center">
            <a type="button" class="btn btn-primary" @click="submit">Submit</a>
        </div>
    </form>
</div>