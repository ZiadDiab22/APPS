<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<body>
  <h1>{{$title}}</h1>
  <p>Date: {{$date}}</p>
  <table>
    <thead>
      <tr>
        <th>id</th>
        <th>user_id</th>
        <th>user_name</th>
        <th>user_email</th>
        <th>file_id</th>
        <th>file_name</th>
        <th>type</th>
        <th>content</th>
        <th>old_content</th>
        <th>operation</th>
        <th>created_at</th>
      </tr>
    </thead>
    <tbody>
      @foreach($data as $value)
      <tr>
        <td>{{ $value->id }}</td>
        <td>{{ $value->user_id }}</td>
        <td>{{ $value->user_name }}</td>
        <td>{{ $value->user_email }}</td>
        <td>{{ $value->file_id }}</td>
        <td>{{ $value->file_name }}</td>
        <td>{{ $value->type }}</td>
        <td>{{ $value->content }}</td>
        <td>{{ $value->old_content }}</td>
        <td>{{ $value->operation }}</td>
        <td>{{ $value->created_at }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <style>
    table {
      border-collapse: collapse;
    }

    td,
    th {
      border: 1px solid black;
    }

    th {
      height: 65px;
      background-color: #FDEB71;
    }

    td {
      background-color: #ABDCEF;
    }
  </style>
</body>

</html>