@extends('layouts.app')

@section('content')

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
                
               
                
                    <div class="alert alert-success mt-4" role="alert">
                        Результат: 
                        
                        <?php
                        if(isset($result["records"][0])){
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
                        }
                        ?>    
                        
                    </div>

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
                        <button type="button" class="btn btn-primary">Выдать комплект</button>
                    </div>
                
            </div>
        </div>
    </div>
</div>


@endsection
