package project.lighthouse.autotests;

import project.lighthouse.autotests.objects.Invoice;
import project.lighthouse.autotests.objects.Product;

import java.util.HashMap;

public class StaticDataCollections {

    public static HashMap<String, Product> products = new HashMap<>();
    public static HashMap<String, Invoice> invoices = new HashMap<>();
    public static String TIMEOUT = "5000";
}
