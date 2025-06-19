<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Поиск книг</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm p-4">
                <h3 class="mb-4 text-center">Введите данные</h3>
                <form method="POST" action="{{ route('search') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="selection">тип поиска</label>
                        <select name="selection" id="selection">
                            <option value="in">I - инвентарный номер (поле 910)</option>
                            <option value="i">I - шифр документа (поле 903)</option>
                            <option value="k">K - ключевые слова</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input 
                           
                          name="inputNumber" 
                          class="form-control" 
                          placeholder="Введите число" 
                          
                          required />
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Выполнить</button>
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
                                    echo "<h5>статус:</h5>".$bookStatus."<br>";
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
                                echo "<h2>Записи в комплекте</h2><ul>";
                                foreach ($complectRecs as $rec) {
                                    echo "<li>".$rec."</li>";
                                }
                                echo "</ul>";
                            }
                        }
                        ?>  
                        
                    </div>
                
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

