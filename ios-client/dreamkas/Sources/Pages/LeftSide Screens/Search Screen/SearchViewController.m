//
//  SearchViewController.m
//  dreamkas
//
//  Created by sig on 30.10.14.
//  Copyright (c) 2014 Dreamkas. All rights reserved.
//

#import "SearchViewController.h"
#import "ProductSearchCell.h"
#import "SearchField.h"

#define RequiredSearchfieldValueLenght 3
#define TableViewDefaultHeight 640

typedef NS_ENUM(NSInteger, kInfoMessageType) {
    kInfoMessageTypeNone = 0,
    kInfoMessageTypeEmptyField,
    kInfoMessageTypeNoResults
};

@interface SearchViewController () <UITextFieldDelegate, KeyboardEventsListenerProtocol>

@property (nonatomic) IBOutlet CustomLabel *infoMsgLabel;
@property (nonatomic) SearchField *searchField;

@end

@implementation SearchViewController

#pragma mark - Инициализация

- (void)initialize
{
    // выключаем для контроллера массовое обновление и лимитированные запросы
    [self setPullDownActionEnabled:NO];
    [self setLimitedQueryEnabled:NO];
    [self becomeKeyboardEventsListener];
}

#pragma mark - View Lifecycle

- (void)viewDidLoad
{
    [super viewDidLoad];
    
    [self.infoMsgLabel setTextColor:DefaultGrayColor];
    
    [self placeSearchField];
}

- (void)viewWillAppear:(BOOL)animated
{
    [super viewWillAppear:animated];
    
    [self.tableViewItem setHidden:YES];
    [self setInfoMessage:kInfoMessageTypeEmptyField];
    
    [self.searchField becomeFirstResponder];
}

- (void)viewDidAppear:(BOOL)animated
{
    [super viewDidAppear:animated];
    
    // ..
}

#pragma mark - Configuration Methods

- (void)placeSearchField
{
    self.searchField = [[SearchField alloc] initWithFrame:CGRectZero];
    [self.searchField setDelegate:self];
    [self.searchField setWidth:600];
    [self.searchField setHeight:DefaultTopPanelHeight];
    [self.searchField setPlaceholder:NSLocalizedString(@"product_search_field_placeholder", nil)];
    self.navigationItem.titleView = self.searchField;
}

- (void)configureLocalization
{
    // ..
}

- (void)configureAccessibilityLabels
{
    // ..
}

- (void)setInfoMessage:(kInfoMessageType)type
{
    [self.infoMsgLabel setHidden:NO];
    
    switch ((kInfoMessageType)type) {
        case kInfoMessageTypeNoResults:
            [self.infoMsgLabel setText:NSLocalizedString(@"product_search_no_results_message", nil)];
            break;
            
        case kInfoMessageTypeEmptyField:
            [self.infoMsgLabel setText:NSLocalizedString(@"product_search_empty_message", nil)];
            break;
            
        default:
            [self.infoMsgLabel setText:@""];
            [self.infoMsgLabel setHidden:YES];
            break;
    }
}

#pragma mark - Методы KeyboardEventsListenerProtocol

- (void)keyboardWillAppear:(NSNotification *)notification
{
    DPLogFast(@"self.tableViewItem = %@", self.tableViewItem);
    
    CGRect keyboard_bounds;
    [[notification.userInfo valueForKey:UIKeyboardFrameBeginUserInfoKey] getValue:&keyboard_bounds];
    
    [self.tableViewItem setHeight:TableViewDefaultHeight - CGRectGetHeight(keyboard_bounds) + DefaultTopPanelHeight];
    [self.tableViewItem reloadData];
}

- (void)keyboardWillDisappear:(NSNotification *)notification
{
    DPLogFast(@"");
    
    [self.tableViewItem setHeight:TableViewDefaultHeight];
    [self.tableViewItem reloadData];
}

#pragma mark - Методы UITextfield Delegate

- (BOOL)textFieldShouldBeginEditing:(UITextField *)textField
{
    DPLogFast(@"");
    return YES;
}

- (void)textFieldDidBeginEditing:(UITextField *)textField
{
    DPLogFast(@"");
}

- (BOOL)textFieldShouldEndEditing:(UITextField *)textField
{
    DPLogFast(@"");
    return YES;
}

- (void)textFieldDidEndEditing:(UITextField *)textField
{
    DPLogFast(@"");
}

- (BOOL)textField:(UITextField *)textField shouldChangeCharactersInRange:(NSRange)range replacementString:(NSString *)string
{
    DPLogFast(@"");
    
    NSMutableString *tmp = [NSMutableString stringWithString:textField.text];
    [tmp replaceCharactersInRange:range withString:string];
    
    if (tmp.length >= RequiredSearchfieldValueLenght) {
        [self.tableViewItem setHidden:NO];
        [self setInfoMessage:kInfoMessageTypeNone];
        
        [self shouldReloadTableView];
        
    }
    else {
        [self.tableViewItem setHidden:YES];
        [self setInfoMessage:kInfoMessageTypeEmptyField];
    }
    
    return YES;
}

- (BOOL)textFieldShouldClear:(UITextField *)textField
{
    DPLogFast(@"");
    return YES;
}

- (BOOL)textFieldShouldReturn:(UITextField *)textField
{
    DPLogFast(@"");
    [self.searchField setText:@"Йогурт"];
//    [textField resignFirstResponder];
    return YES;
}

#pragma mark - Методы CustomTableViewController

/**
 *  Kласс ячейки таблицы
 */
- (Class)cellClass
{
    return [ProductSearchCell class];
}

/**
 *  Метод возвращает название класса, чьи экземпляры выбираются из БД и выводятся в таблице
 */
- (Class)fetchClass
{
    return [ProductModel class];
}

/**
 * Метод возвращает название параметра, по которому происходит сортировка при выборке из БД
 */
- (NSString*)fetchSortedField
{
    return @"name";
}

/**
 *  Метод показывает направление сортировки при выборке
 *  (YES - по возрастанию, NO - по убыванию)
 */
- (BOOL)isFetchAscending
{
    return YES;
}

/**
 *  Метод возвращает предикат, по которому происходит фильтрация при выборке из БД
 */
- (NSPredicate*)fetchPredicate
{
    NSMutableArray *argument_array = [NSMutableArray new];
    NSMutableArray *format_array = [NSMutableArray new];
    NSPredicate *predicate = nil;
    
    // формируем выборку по названию
    [format_array addObject:@"name CONTAINS[c] %@"];
    [argument_array addObject:self.searchField.text];
    
    // формируем выборку по штрих-коду
    [format_array addObject:@"barcode CONTAINS[c] %@"];
    [argument_array addObject:self.searchField.text];
    
    // формируем выборку по sku
    [format_array addObject:@"sku CONTAINS[c] %@"];
    [argument_array addObject:self.searchField.text];
    
    if (self.tableViewItem.isHidden) {
        // формируем предикат с заведомо пустой выдачей
        predicate = [NSPredicate predicateWithValue:NO];
    }
    else {
        // формируем предикат по полученным данным
        predicate = [NSPredicate predicateWithFormat:[format_array componentsJoinedByString:@" OR "]
                                       argumentArray:argument_array];
    }
    
    return predicate;
}

/**
 *  Метод, инициирующий загрузку данных с сервера
 */
- (void)requestDataFromServer
{
    [super requestDataFromServer];
    
    __weak typeof(self)weak_self = self;
    [NetworkManager requestProductsByQuery:self.searchField.text
                              onCompletion:^(NSArray *data, NSError *error) {
                                  __strong typeof(self)strong_self = weak_self;
                                  
                                  if (error != nil) {
                                      [strong_self onMappingFailure:error];
                                      return;
                                  }
                                  [strong_self onMappingCompletion:data];
                              }];
}

/**
 *  Установка высоты ячейкам таблицы
 */
- (CGFloat)tableView:(UITableView *)tableView heightForRowAtIndexPath:(NSIndexPath *)indexPath
{
    NSString *cell_identifier = [NSString stringWithFormat:@"Cell_%@", [self fetchClass]];
    return [ProductSearchCell cellHeight:tableView
                          cellIdentifier:cell_identifier
                                   model:[self.fetchedResultsController objectAtIndexPath:indexPath]];
}

@end