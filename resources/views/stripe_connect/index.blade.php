<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stripe Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
</head>

<body>
    <form action="{{ route('stripe.connect.create') }}" method="POST">
        @csrf
        <div class="container mt-5">

            <h1>Stripe Connect</h1>

            <span class="text-primary">Available balance : </span> ${{ number_format($available / 100, 2) }}
            {{ strtoupper($currency) }}<br>
            <span>Pending balance : </span> ${{ number_format($pending / 100, 2) }} {{ strtoupper($currency) }}<br>

            @if (session('account_id'))
                <div class="alert alert-success">
                    Account ID : {{ session()->get('account_id') }}
                </div>
            @endif


            <div class="form-group mt-3">
                <label for="">Email</label>
                <input type="text" class="form-control" name="email" required>
            </div>

            <div class="form-group mt-3">
                <label for="">First Name</label>
                <input type="text" class="form-control" name="first_name" required>
            </div>

            <div class="form-group mt-3">
                <label for="">Last Name</label>
                <input type="text" class="form-control" name="last_name" required>
            </div>

            <div class="form-group">
                <label for="dob">Date of Birth</label>
                <div class="row">
                    <div class="col-md-4">
                        <input type="number" class="form-control" name="day" id="dob_day" placeholder="Day"
                            min="1" max="31" required>
                    </div>
                    <div class="col-md-4">
                        <input type="number" class="form-control" name="month" id="dob_month" placeholder="Month"
                            min="1" max="12" required>
                    </div>
                    <div class="col-md-4">
                        <input type="number" class="form-control" name="year" id="dob_year" placeholder="Year"
                            min="1900" max="2025" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="mt-3 btn btn-primary">Create Stripe Connect</button>
    </form>
    <hr>
    <form method="POST" class="mt-3" action="{{ route('stripe.transfer') }}">

        <h1>Send Funds</h1>

        @if (session('success'))
            <div class="alert alert-info">
                {{ session('success') }}
            </div>
        @endif
        @csrf

        <div class="form-group">
            <label for="account_id">Account ID</label>
            <select name="account_id" class="form-control">
                @foreach ($accounts as $account)
                    <option value="{{ $account['id'] }}">{{ $account['first_name'] }} {{ $account['last_name'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Amount </label>
            <input type="number" class="form-control" id="amount" name="amount" min="0" required>
        </div>

        <button type="submit" class="btn btn-primary mt-2">Send </button>
    </form>
    <hr>
    <table class="table table-striped mt-5">
        <thead>
            <tr>
                <th></th>
                <th scope="col">Account ID</th>
                <th scope="col">Email</th>
                <th scope="col">Name</th>
                <th scope="col">Date of Birth</th>
                {{-- <th scope="col">Status</th> --}}
                <th scope="col">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($accounts as $account)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $account['id'] }}</td>
                    <td>{{ $account['email'] ?? 'N/A' }}</td>
                    <td>{{ $account['first_name'] ?? 'N/A' }} {{ $account['last_name'] ?? 'N/A' }}</td>
                    <td>
                        @if ($account['dob'])
                            {{ $account['dob']->day }}/{{ $account['dob']->month }}/{{ $account['dob']->year }}
                        @else
                            N/A
                        @endif
                    </td>
                    {{-- <td>
                        
                        <span class="badge {{ $account['status'] === 'Enabled' ? 'bg-success' : 'bg-danger' }}">
                            {{ $account['status'] }}
                        </span>
                    </td> --}}
                    <td>
                        ${{ number_format($account['available'] / 100, 2) }} {{ strtoupper($account['currency']) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <hr>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($)
        </tbody>
    </table>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
</body>

</html>
