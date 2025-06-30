@extends('layouts.app')

@section('content')
<script src="js/jquery-3.5.1.slim.min.js"></script>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm p-4">
                <h3 class="mb-4 text-center">Введите данные</h3>
<!--               Форма поиска читателя                   -->

                <form method="POST" action="{{ route('searchReader') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <input 
                           
                          name="reader" 
                          class="form-control" 
                          placeholder="ID читателя" 
                          
                          required />
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Найти</button>
                    </div>
                </form>
                <br>
                <p>
                    
                @if(session('reader'))
                    читатель: {{ session('reader') }}
                @endif
                    
                </p>
                <br>
<!--               Форма поиска экземпляра                   -->
                <form method="POST" action="{{ route('search') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <input 
                           
                          name="inputNumber" 
                          class="form-control" 
                          placeholder="Введите инвентарный номер" 
                          
                          required />
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Найти</button>
                    </div>
                </form>
                                       
                        <?php
                        if(isset($result["records"][0])){

                            echo '<div class="alert alert-success mt-4" role="alert">';
                            
                            //dd($result["records"]);
                            foreach ($result["records"] as $item) {
                                echo "<h5>экземпляр:</h5>".$item[1]."<br>";
                                echo "<h5>инвентарный номер:</h5>".$invNum."<br>";
                                echo "<h5>инвентарный номер из БД:</h5>".$invNumFromDB."<br>";
                                if(isset($bookStatus)){
                                    
                                    if($bookStatus=="Утерян" || $bookStatus=="Списан"){
                                        
                                        echo "<div style='color:red;'><h5>статус:</h5>".$bookStatus."</div><br>";    
                                    }else{
                                        echo "<h5>статус:</h5>".$bookStatus."<br>";
                                    }
                                }else{
                                    echo "<h5>не удалось получить статус</h5>";
                                }
                            
                            echo "<br><hr><br>";
                            }
                        echo '</div>';
                        }
                        ?>    
                        
                    

                    <div>
                                                
                        <?php
                        if(isset($complectRecs)){
                            if(count($complectRecs) > 0){                            
                                echo "<h2>Записи в комплекте</h2><ol>";
                                foreach ($complectRecs as $rec) {
                                    echo "<li>".$rec."</li>";
                                }
                                echo "</ol>";
                            }
                        
                        }
                        ?>  
                        @if(isset($complectRecs))
                        <a href="/giveComplect" class="btn btn-primary">Выдать комплект</a>
                        <div class="container mt-5">
                            <form action="/giveComplect" method="post">
                                <div class="form-group">
                                    <label for="datepicker">Календарь</label>
                                    <input type="text" class="form-control" id="datepicker" name="day" placeholder="Выберите дату">
                                </div>
                                <button type="submit" class="btn btn-primary" name="send">Выдать комплект</button>
                            </form>
                        </div>
                        <script>
                            $(document).ready(function() {
                                $('#datepicker').datepicker({
                                format: 'dd.mm.yyyy', // Формат даты
                                language: 'ru', // Язык
                                autoclose: true // Закрытие после выбора даты
                                });
                            });
                        </script>
                        
                        <script src="js/bootstrap.bundle.min.js"></script>
                        <script src="js/bootstrap-datepicker.min.js"></script>
                        <script src="js/bootstrap-datepicker.ru.min.js"></script>
                        @endif
                    </div>
                
            </div>
        </div>
    </div>
</div>


@endsection
