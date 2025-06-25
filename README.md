# Quiz API Documentation

## Overview
This API provides access to a quiz database with AI-powered question generation capabilities. Users can retrieve questions, filter by categories, and generate new questions using AI.

**Base URL:** `[https://api.umaars.com/quiz.php]`

---

## Authentication
All API requests require an API key in the header.

**Header Required:**
```
Api-Key: YOUR_API_KEY_HERE
```

**Getting API Key:**
- Contact the admin to get your API key
- API keys are managed through the admin panel

---

## Endpoints

### 1. Get All Questions
Retrieve all questions from the database.

**Endpoint:** `GET /quiz.php`

**Headers:**
```
Api-Key: YOUR_API_KEY_HERE
```

**Response:**
```json
[
  {
    "id": 1,
    "question": "What is the capital of France?",
    "option_a": "London",
    "option_b": "Paris",
    "option_c": "Berlin",
    "option_d": "Madrid",
    "correct_option": "B",
    "category": "Geography",
    "created_by_ai": 0
  },
  {
    "id": 2,
    "question": "Which programming language is used for Android development?",
    "option_a": "Python",
    "option_b": "Java",
    "option_c": "C++",
    "option_d": "JavaScript",
    "correct_option": "B",
    "category": "Programming",
    "created_by_ai": 1
  }
]
```

**Java/Android Example:**
```java
import okhttp3.*;
import org.json.JSONArray;
import org.json.JSONObject;
import java.io.IOException;

public class QuizApiClient {
    private static final String BASE_URL = "http://your-domain.com/quiz-api/";
    private static final String API_KEY = "YOUR_API_KEY_HERE";
    
    public void getAllQuestions(Callback callback) {
        OkHttpClient client = new OkHttpClient();
        
        Request request = new Request.Builder()
            .url(BASE_URL + "quiz.php")
            .addHeader("Api-Key", API_KEY)
            .build();
            
        client.newCall(request).enqueue(callback);
    }
}

// Usage
QuizApiClient client = new QuizApiClient();
client.getAllQuestions(new Callback() {
    @Override
    public void onResponse(Call call, Response response) throws IOException {
        String jsonData = response.body().string();
        JSONArray questions = new JSONArray(jsonData);
        
        for (int i = 0; i < questions.length(); i++) {
            JSONObject question = questions.getJSONObject(i);
            int id = question.getInt("id");
            String questionText = question.getString("question");
            String category = question.getString("category");
            // Process each question...
        }
    }
    
    @Override
    public void onFailure(Call call, IOException e) {
        // Handle error
    }
});
```

---

### 2. Get Questions by Category
Filter questions by specific category.

**Endpoint:** `GET /quiz.php?category={category_name}`

**Headers:**
```
Api-Key: YOUR_API_KEY_HERE
```

**Parameters:**
- `category` (string): The category name to filter by

**Response:**
```json
[
  {
    "id": 1,
    "question": "What is the capital of France?",
    "option_a": "London",
    "option_b": "Paris",
    "option_c": "Berlin",
    "option_d": "Madrid",
    "correct_option": "B",
    "category": "Geography",
    "created_by_ai": 0
  }
]
```

**Java/Android Example:**
```java
public void getQuestionsByCategory(String category, Callback callback) {
    OkHttpClient client = new OkHttpClient();
    
    String url = BASE_URL + "quiz.php?category=" + category;
    Request request = new Request.Builder()
        .url(url)
        .addHeader("Api-Key", API_KEY)
        .build();
        
    client.newCall(request).enqueue(callback);
}

// Usage
client.getQuestionsByCategory("Geography", new Callback() {
    @Override
    public void onResponse(Call call, Response response) throws IOException {
        String jsonData = response.body().string();
        JSONArray questions = new JSONArray(jsonData);
        // Process geography questions only...
    }
    
    @Override
    public void onFailure(Call call, IOException e) {
        // Handle error
    }
});
```

---

### 3. Get Available Categories
Get list of all available categories in the database.

**Endpoint:** `GET /quiz.php`

**Headers:**
```
Api-Key: YOUR_API_KEY_HERE
```

**Java/Android Example:**
```java
public Set<String> getAvailableCategories() {
    Set<String> categories = new HashSet<>();
    
    // First get all questions
    getAllQuestions(new Callback() {
        @Override
        public void onResponse(Call call, Response response) throws IOException {
            String jsonData = response.body().string();
            JSONArray questions = new JSONArray(jsonData);
            
            for (int i = 0; i < questions.length(); i++) {
                JSONObject question = questions.getJSONObject(i);
                String category = question.optString("category", "");
                if (!category.isEmpty()) {
                    categories.add(category);
                }
            }
        }
        
        @Override
        public void onFailure(Call call, IOException e) {
            // Handle error
        }
    });
    
    return categories;
}
```

---

### 4. Generate AI Question
Generate a new question using AI.

**Endpoint:** `POST /generate.php`

**Headers:**
```
Api-Key: YOUR_API_KEY_HERE
Content-Type: application/x-www-form-urlencoded
```

**Parameters:**
- `prompt` (string): Description of the question you want to generate

**Response:**
```json
{
  "success": true,
  "message": "Question added successfully!",
  "question": "What is the primary programming language for Android development?",
  "options": {
    "A": "Python",
    "B": "Java",
    "C": "Swift",
    "D": "Kotlin"
  },
  "correct_option": "B",
  "category": "Programming",
  "id": 15
}
```

**Java/Android Example:**
```java
public void generateAIQuestion(String prompt, Callback callback) {
    OkHttpClient client = new OkHttpClient();
    
    RequestBody formBody = new FormBody.Builder()
        .add("prompt", prompt)
        .build();
        
    Request request = new Request.Builder()
        .url(BASE_URL + "generate.php")
        .addHeader("Api-Key", API_KEY)
        .post(formBody)
        .build();
        
    client.newCall(request).enqueue(callback);
}

// Usage
client.generateAIQuestion("Create a question about Android development", new Callback() {
    @Override
    public void onResponse(Call call, Response response) throws IOException {
        String jsonData = response.body().string();
        JSONObject result = new JSONObject(jsonData);
        
        if (result.getBoolean("success")) {
            String question = result.getString("question");
            String category = result.getString("category");
            String correctOption = result.getString("correct_option");
            
            JSONObject options = result.getJSONObject("options");
            String optionA = options.getString("A");
            String optionB = options.getString("B");
            String optionC = options.getString("C");
            String optionD = options.getString("D");
            
            // Use the generated question...
        }
    }
    
    @Override
    public void onFailure(Call call, IOException e) {
        // Handle error
    }
});
```

---

### 5. Add Manual Question
Add a question manually to the database.

**Endpoint:** `POST /quiz.php`

**Headers:**
```
Api-Key: YOUR_API_KEY_HERE
Content-Type: application/x-www-form-urlencoded
```

**Parameters:**
- `question` (string): The question text
- `option_a` (string): Option A
- `option_b` (string): Option B
- `option_c` (string): Option C
- `option_d` (string): Option D
- `correct_option` (string): Correct option (A, B, C, or D)
- `category` (string): Category name

**Response:**
```json
{
  "success": true,
  "message": "Question added successfully!"
}
```

**Java/Android Example:**
```java
public void addManualQuestion(String question, String optionA, String optionB, 
                            String optionC, String optionD, String correctOption, 
                            String category, Callback callback) {
    OkHttpClient client = new OkHttpClient();
    
    RequestBody formBody = new FormBody.Builder()
        .add("question", question)
        .add("option_a", optionA)
        .add("option_b", optionB)
        .add("option_c", optionC)
        .add("option_d", optionD)
        .add("correct_option", correctOption)
        .add("category", category)
        .build();
        
    Request request = new Request.Builder()
        .url(BASE_URL + "quiz.php")
        .addHeader("Api-Key", API_KEY)
        .post(formBody)
        .build();
        
    client.newCall(request).enqueue(callback);
}
```

---

## Error Responses

All endpoints may return error responses in this format:

```json
{
  "error": "Error message description"
}
```

**Common Error Codes:**
- `401 Unauthorized`: Missing or invalid API key
- `400 Bad Request`: Invalid parameters
- `500 Internal Server Error`: Server error

**Java/Android Error Handling:**
```java
@Override
public void onResponse(Call call, Response response) throws IOException {
    String jsonData = response.body().string();
    
    if (response.isSuccessful()) {
        // Process successful response
        JSONArray questions = new JSONArray(jsonData);
        // Handle data...
    } else {
        // Handle error response
        JSONObject error = new JSONObject(jsonData);
        String errorMessage = error.getString("error");
        
        switch (response.code()) {
            case 401:
                // Handle unauthorized - check API key
                break;
            case 400:
                // Handle bad request - check parameters
                break;
            case 500:
                // Handle server error
                break;
        }
    }
}
```

---

## Complete Android Example

Here's a complete Android example showing how to use the API:

```java
public class QuizApiManager {
    private static final String BASE_URL = "http://your-domain.com/quiz-api/";
    private static final String API_KEY = "YOUR_API_KEY_HERE";
    private OkHttpClient client;
    
    public QuizApiManager() {
        client = new OkHttpClient();
    }
    
    // Get all questions
    public void getAllQuestions(QuizCallback callback) {
        Request request = new Request.Builder()
            .url(BASE_URL + "quiz.php")
            .addHeader("Api-Key", API_KEY)
            .build();
            
        client.newCall(request).enqueue(new Callback() {
            @Override
            public void onResponse(Call call, Response response) throws IOException {
                if (response.isSuccessful()) {
                    String jsonData = response.body().string();
                    callback.onSuccess(jsonData);
                } else {
                    callback.onError("HTTP " + response.code());
                }
            }
            
            @Override
            public void onFailure(Call call, IOException e) {
                callback.onError(e.getMessage());
            }
        });
    }
    
    // Get questions by category
    public void getQuestionsByCategory(String category, QuizCallback callback) {
        String url = BASE_URL + "quiz.php?category=" + category;
        Request request = new Request.Builder()
            .url(url)
            .addHeader("Api-Key", API_KEY)
            .build();
            
        client.newCall(request).enqueue(new Callback() {
            @Override
            public void onResponse(Call call, Response response) throws IOException {
                if (response.isSuccessful()) {
                    String jsonData = response.body().string();
                    callback.onSuccess(jsonData);
                } else {
                    callback.onError("HTTP " + response.code());
                }
            }
            
            @Override
            public void onFailure(Call call, IOException e) {
                callback.onError(e.getMessage());
            }
        });
    }
    
    // Generate AI question
    public void generateQuestion(String prompt, QuizCallback callback) {
        RequestBody formBody = new FormBody.Builder()
            .add("prompt", prompt)
            .build();
            
        Request request = new Request.Builder()
            .url(BASE_URL + "generate.php")
            .addHeader("Api-Key", API_KEY)
            .post(formBody)
            .build();
            
        client.newCall(request).enqueue(new Callback() {
            @Override
            public void onResponse(Call call, Response response) throws IOException {
                if (response.isSuccessful()) {
                    String jsonData = response.body().string();
                    callback.onSuccess(jsonData);
                } else {
                    callback.onError("HTTP " + response.code());
                }
            }
            
            @Override
            public void onFailure(Call call, IOException e) {
                callback.onError(e.getMessage());
            }
        });
    }
    
    public interface QuizCallback {
        void onSuccess(String jsonData);
        void onError(String error);
    }
}

// Usage in Activity or Fragment
public class MainActivity extends AppCompatActivity {
    private QuizApiManager apiManager;
    
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        
        apiManager = new QuizApiManager();
        
        // Get all questions
        apiManager.getAllQuestions(new QuizApiManager.QuizCallback() {
            @Override
            public void onSuccess(String jsonData) {
                try {
                    JSONArray questions = new JSONArray(jsonData);
                    // Process questions...
                    Log.d("QuizAPI", "Found " + questions.length() + " questions");
                } catch (JSONException e) {
                    Log.e("QuizAPI", "JSON parsing error", e);
                }
            }
            
            @Override
            public void onError(String error) {
                Log.e("QuizAPI", "API Error: " + error);
            }
        });
        
        // Get questions by category
        apiManager.getQuestionsByCategory("Programming", new QuizApiManager.QuizCallback() {
            @Override
            public void onSuccess(String jsonData) {
                // Handle programming questions...
            }
            
            @Override
            public void onError(String error) {
                // Handle error...
            }
        });
        
        // Generate AI question
        apiManager.generateQuestion("Create a question about Java programming", 
            new QuizApiManager.QuizCallback() {
                @Override
                public void onSuccess(String jsonData) {
                    try {
                        JSONObject result = new JSONObject(jsonData);
                        if (result.getBoolean("success")) {
                            String question = result.getString("question");
                            String category = result.getString("category");
                            // Use the generated question...
                        }
                    } catch (JSONException e) {
                        Log.e("QuizAPI", "JSON parsing error", e);
                    }
                }
                
                @Override
                public void onError(String error) {
                    // Handle error...
                }
            });
    }
}
```

---

## Dependencies

Add these dependencies to your `build.gradle` file:

```gradle
dependencies {
    implementation 'com.squareup.okhttp3:okhttp:4.9.3'
    implementation 'org.json:json:20210307'
}
```

---

## Notes

1. **API Key Security**: Keep your API key secure and don't share it publicly
2. **Rate Limiting**: Be mindful of API usage to avoid overwhelming the server
3. **Error Handling**: Always implement proper error handling for network requests
4. **Async Operations**: All network calls should be made on background threads
5. **Categories**: The AI will automatically use existing categories when generating questions

---

## Support

For API key requests or technical support, contact the admin through the admin panel.
