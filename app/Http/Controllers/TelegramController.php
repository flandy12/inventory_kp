<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockInExport;
use App\Exports\StockOutExport;


class TelegramController extends Controller
{
    public function webhook(Request $request)
    {
        \Log::info("Webhook masuk dari Telegram");
        \Log::info($request->all());

        $message = $request->input('message.text');
        $chatId = $request->input('message.chat.id');

        if ($message === '/stockIn_export') {
            $this->sendExcelToTelegram($chatId);
            // $csvText = "name,stock\nitem1,10\nitem2,20";
            // $this->sendCSV($chatId, $csvText, 'stockIn.csv');
        }

        if ($message === '/stockOut_export') {
            $this->sendExcelToTelegramStockOut($chatId);
            // $csvText = "name,stock\nitem1,10\nitem2,20";
            // $this->sendCSV($chatId, $csvText, 'stockIn.csv');
        }


        return response()->json(['status' => 'ok']);
    }

    private function sendCSV($chatId, $csvText, $filename)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $url = "https://api.telegram.org/bot{$botToken}/sendDocument";

        $tempFile = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tempFile, $csvText);

        Http::attach(
            'document',
            file_get_contents($tempFile),
            $filename
        )->asMultipart()->post($url, [
            'chat_id' => $chatId,
        ]);

        unlink($tempFile);
    }

    public function sendExcelToTelegram($chatId)
    {
        $filename = 'stockin.xlsx';
        $tempPath = storage_path('app/' . $filename);

        // Generate file Excel dan simpan sementara di storage/app/
        Excel::store(new StockInExport, $filename);

        $botToken = env('TELEGRAM_BOT_TOKEN');
        $url = "https://api.telegram.org/bot{$botToken}/sendDocument";

        // Kirim file ke Telegram
        $response = Http::attach(
            'document',
            file_get_contents($tempPath),
            $filename
        )->post($url, [
            'chat_id' => $chatId,
        ]);

        // Hapus file sementara
        unlink($tempPath);

        \Log::info('Response kirim Excel ke Telegram: ' . $response->body());
    }

    public function sendExcelToTelegramStockOut($chatId)
    {
        $filename = 'stockout.xlsx';
        $tempPath = storage_path('app/' . $filename);

        // Generate file Excel dan simpan sementara di storage/app/
        Excel::store(new StockOutExport, $filename);

        $botToken = env('TELEGRAM_BOT_TOKEN');
        $url = "https://api.telegram.org/bot{$botToken}/sendDocument";

        // Kirim file ke Telegram
        $response = Http::attach(
            'document',
            file_get_contents($tempPath),
            $filename
        )->post($url, [
            'chat_id' => $chatId,
        ]);

        // Hapus file sementara
        unlink($tempPath);

        \Log::info('Response kirim Excel ke Telegram: ' . $response->body());
    }



}
